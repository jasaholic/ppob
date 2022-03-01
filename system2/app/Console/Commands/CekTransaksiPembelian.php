<?php

namespace App\Console\Commands;

use DB, Exception, DigiFlazz, Log, Mail;
use Illuminate\Console\Command;
use App\User;
use App\AppModel\Transaksi;
use App\AppModel\SMSGateway;
use App\AppModel\Temptransaksi;
use App\AppModel\Setting;
use App\AppModel\Mutasi;
use App\AppModel\Komisiref;
use App\AppModel\Pembelianproduk;

class CekTransaksiPembelian extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pembelian:cek';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek Transaksi Pembelian';
    
    protected $personal_role   = 1;
    protected $admin_role      = 2;
    protected $agen_role       = 3;
    protected $enterprise_role = 4;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        DB::statement("SET session wait_timeout=300");
        
        $temp_transaksiCount = (int) Temptransaksi::count();
        
        if( $temp_transaksiCount > 0 )
        {
            $temp_transaksi = Temptransaksi::get();

            foreach($temp_transaksi as $tmp)
            {
                DB::beginTransaction();
                        
                try
                {
                    $transaksis = Transaksi::whereNull('tagihan_id')->where('order_id', $tmp->transaksi_id)->where('status', 0)->first();

                    if($transaksis)
                    {
                        # MASTER DATA
                        $response = [
                            'success' => false,
                            'data' => [
                                'trx_id' => 0,
                                'product_code' => NULL,
                                'status' => 0,
                                'sn' => NULL,
                                'customer_id' => NULL,
                                'phone' => NULL,
                                'note' => 'Error [#1]',
                            ],
                        ];
                    
                        $is_pln = !empty(trim($transaksis->mtrpln, '-')) ? true : false;
                        
                        if( $transaksis->apiserver_id == 1 )
                        {
                            // Cek status sama dengan order ulang dengan refid yang sama
                            if( $is_pln )
                            {
                                $check = DigiFlazz::order($transaksis->order_id, $transaksis->code, $transaksis->sequence_id, null, $transaksis->mtrpln);
                            }
                            else
                            {
                                $check = DigiFlazz::order($transaksis->order_id, $transaksis->code, $transaksis->sequence_id, $transaksis->target);
                            }
                            
                            $check = json_decode($check);
                            
                            // if( $check->success !== true ) {
                            //     continue;
                            // }
                            
                            $response['success'] = true; // dibuat selalu true
                            
                            if( is_null($check->response) ) {
                                continue;
                            }
                            
                            $data = $check->response->data;
                            $status = strtolower($data->status);
                            
                            if( $transaksis->sequence_id > 1 ) 
                            {
                                if( $data->customer_no != $transaksis->sequence_id.'.'.($is_pln ? $transaksis->mtrpln : $transaksis->target) ) {
                                    continue;
                                }
                            }
                            
                            $customer_no = explode('.', $data->customer_no);
                            
                            if( count($customer_no) > 1 )
                            {
                                if( preg_match('/^[\d]+$/i', $customer_no[0]) ) // if sequence detected
                                {
                                    $sl = strlen($customer_no[0]) + 1;
                                    $customer_no = substr($data->customer_no, $sl);
                                }
                                else
                                {
                                    $customer_no = $data->customer_no;
                                }
                            }
                            else
                            {
                                $customer_no = $data->customer_no;
                            }
                            
                            $response['data'] = [
                                'trx_id' => $data->ref_id,
                                'product_code' => $data->buyer_sku_code,
                                'status' => $status == 'sukses' ? 1 : ($status == 'gagal' ? 2 : 0),
                                'sn' => isset($data->sn) ? $data->sn : null,
                                'customer_id' => $is_pln ? $customer_no : NULL,
                                'phone' => $is_pln ? NULL : $customer_no,
                                'note' => $data->message
                            ];
                        }
                        else
                        {
                            // Unknown server
                            continue;
                        }
                        
                        $user = User::findOrFail($transaksis->user_id);
                        $hargaproduk = $transaksis->total;
                        // $is_aigo = preg_match('/^(aim|aigo|axo)[0-9]+$/i', $response['data']['product_code']) ? true : false;
                        
                        if( $response['data']['product_code'] == 'AD15' || $response['data']['product_code'] == 'AD16' || $response['data']['product_code'] == 'AD17' || $response['data']['product_code'] == 'AD18' || $response['data']['product_code'] == 'AD19' )
                        {
                            $addmessage = "Aktivasi paket Axis OWSEM ketik *838*".$response['sn']."#";
                        }
                        else
                        {
                            $addmessage = '';
                        }
                        
                        if( $response['success'] === true )
                        {
                            $data = $response['data'];
                           
                            if( $data['trx_id'] == $tmp->transaksi->order_id )
                            {
                                if( $data['status'] == 1 ) // SUKSES
                                {
                                    $tmp->delete();
                                    
                                    if( $transaksis->status == 0 && $transaksis->jenis_transaksi == 'otomatis')
                                    {
                                        if( $is_pln )
                                        {
                                            if( $user->roles[0]->id == $this->personal_role )
                                            {
                                                SMSGateway::send($transaksis->target, 'Pembelian '.$transaksis->produk.' ke '.$data['customer_id'].' sukses. SN : '.$data['sn']);
                                            }
                                        }
    
                                        $transaksis->token = $data['sn'];
                                        $transaksis->note = $is_pln ? "Trx ".$data['product_code']." ".$data['customer_id']." Sukses. SN : ".$data['sn'] : "Trx ".$data['product_code']." ".$data['phone']." Sukses. SN : ".$data['sn'].' '.$addmessage;
                                        $transaksis->status = 1;
                                        $transaksis->callback_sent = 0;
    
                                        Temptransaksi::where('transaksi_id', $transaksis->order_id)->delete();
    
    
                                         //BONUS REFERRAL
                                        if(!empty($user->referred_by) || $user->referred_by != 0)
                                        {
                                            $dataKomisi_ref       = Setting::settingsBonus(2);
                                            $ref_user             = $user->referred_by;
                                            $getDataRef           = User::where('id',$ref_user)->first();
                                            $sadlo_ref            = $getDataRef->saldo;
                                            $komisi_ref           = $dataKomisi_ref->komisi;
                                            $akumulasi_komisi_ref = intval($sadlo_ref) + intval($komisi_ref);
                    
                                            DB::table('users')
                                                ->where('affiliate_id', $ref_user)
                                                ->update([
                                                    'saldo' => $akumulasi_komisi_ref,
                                                    ]);
                    
                                            DB::table('mutasis_komisi')
                                                 ->insert([
                                                      'user_id'      => $getDataRef->id,
                                                      'from_reff_id' => $user->id,
                                                      'komisi'       => $komisi_ref,
                                                      'jenis_komisi' => 2,
                                                      'note'         => "BONUS TRANSAKSI REFERRAL Trx ".$response['data']['product_code'],
                                                      'created_at'   => date('Y-m-d H:i:s'),
                                                      'updated_at'   => date('Y-m-d H:i:s'),
                                                    ]);
                    
                                            $mutasiRewardReff = new Mutasi();
                                            $mutasiRewardReff->user_id = $getDataRef->id;
                                            $mutasiRewardReff->trxid = $transaksis->id;
                                            $mutasiRewardReff->type = 'credit';
                                            $mutasiRewardReff->nominal = $komisi_ref;
                                            $mutasiRewardReff->saldo  = intval($sadlo_ref) + intval($komisi_ref);
                                            $mutasiRewardReff->note  = "BONUS TRANSAKSI REFERRAL (".$user->name.", #".$transaksis->id.")";
                                            $mutasiRewardReff->save();
                                        }
    
                                        // PROSES BONUS KOMISI
                                        if($user->roles[0]->id == $this->agen_role){
                                            if($user->referred_by != NULL)
                                            {
                                                 $ref_user             = $user->referred_by;
                                                 $getDataRef           = User::where('id',$ref_user)->first();
                                                 
                                                 if($getDataRef->roles[0]->id == $this->enterprise_role){
                                                    $sadlo_ref            = $getDataRef->saldo;
                                             
                                                    $komisi_ref           = $transaksis->harga_markup;
                                                    $akumulasi_komisi_ref = $sadlo_ref  + $komisi_ref;
                                                
                                                    $getDataRef->update([
                                                            'saldo'=>$akumulasi_komisi_ref
                                                        ]);
                                                                     
                                                    
                                                     DB::table('mutasis_komisi')
                                                          ->insert([
                                                               'user_id'      => $getDataRef->id,
                                                               'from_reff_id' => $user->id,
                                                               'komisi'       => $komisi_ref,
                                                               'jenis_komisi' => 2,
                                                               'note'         => "BONUS TRANSAKSI REFERRAL (".$user->name.", #".$transaksis->id.") | trx ".$response['data']['product_code'],
                                                               'created_at'   => date('Y-m-d H:i:s'),
                                                               'updated_at'   => date('Y-m-d H:i:s'),
                                                             ]);
                                    
                                                     $mutasiRewardReff = new Mutasi();
                                                     $mutasiRewardReff->user_id = $getDataRef->id;
                                                     $mutasiRewardReff->trxid = $transaksis->order_id;
                                                     $mutasiRewardReff->type = 'credit';
                                                     $mutasiRewardReff->nominal = $komisi_ref;
                                                     $mutasiRewardReff->saldo  = $getDataRef ->saldo;
                                                     $mutasiRewardReff->note  = "BONUS TRANSAKSI REFERRAL (".$user->name.", #".$transaksis->id.")";
                                                     $mutasiRewardReff->save();   
                                                 }
                                            }   
                                        }
                                    }
                                }
                                elseif( $data['status'] == 2 ) // GAGAL
                                {
                                    $tmp->delete();
                                    
                                    if( $transaksis->status == 0 )
                                    {
                                        $user->refresh();
                                        $sisaSaldo = $user->saldo + $hargaproduk;
                                        $user->saldo = $sisaSaldo;
                                        
                                        $transaksis->note = (!preg_match('/saldo/i', $data['note']) ? $data['note'] : 'Produk sedang gangguan').". Saldo dikembalikan";
                                        $transaksis->saldo_after_trx = $transaksis->saldo_before_trx;
                                        $transaksis->status = 2;
                                        $transaksis->callback_sent = 0;
    
                                        $mutasi = new Mutasi();
                                        $mutasi->user_id = $user->id;
                                        $mutasi->trxid = $transaksis->id;
                                        $mutasi->type = 'credit';
                                        $mutasi->nominal = $hargaproduk;
                                        $mutasi->saldo  = $sisaSaldo;
                                        $mutasi->note  = $is_pln ? 'TRANSAKSI '.$data['product_code'].' '.$data['customer_id'].' GAGAL' : 'TRANSAKSI '.$data['product_code'].' '.$data['phone'].' GAGAL';
                                        $mutasi->save();
                                        
                                    }
                                }
                            }
                        }
    
                        $user->save();
                        $transaksis->save();
                    }
                    
                    DB::commit();
                }
                catch(Exception $e)
                {
                    DB::rollBack();
                    
                    Log::error($e);
                }
            }
        }
    }
    
}

?>