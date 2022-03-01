<?php

namespace App\Console\Commands;

use DB, Exception, Log, DigiFlazz;
use Illuminate\Console\Command;
use App\User;
use App\AppModel\Transaksi;
use App\AppModel\Tagihan;
use App\AppModel\Temptransaksi;
use App\AppModel\Mutasi;
use App\AppModel\SMSGateway;
use Carbon\Carbon;
use App\AppModel\Setting;
use App\AppModel\Komisiref;
use App\AppModel\Pembayaranproduk;

class CekTransaksiPembayaran extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pembayaran:cek';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek Transaksi Pembayaran';
    
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
        Tagihan::where('status', 0)
                ->where('expired', 1)
                ->where('created_at', '<=', Carbon::now()->subHours(2)->format('Y-m-d H:i:s'))
                ->update([
                    'status'    => 3,
                    'expired'   => 0
                ]);
        
        $temp_transaksiCount = (int) Temptransaksi::count();
       
        if($temp_transaksiCount > 0)
        {
            $temp_transaksi = Temptransaksi::with('transaksi')
                ->whereHas('transaksi', function($trx) {
                    $trx->whereNotNull('tagihan_id');
                })
                ->get();
          
            foreach($temp_transaksi as $tmp)
            {
                if( empty($tmp->transaksi) ) {
                    $tmp->delete();
                    continue;
                }
                elseif( empty($tmp->transaksi->tagihan_id) ) {
                    continue;
                }
                
                DB::beginTransaction();
                
                try
                {
                    $transaksis = Transaksi::where('id', $tmp->transaksi_id)->first();
                    $tagihan = Tagihan::where('tagihan_id', $tmp->transaksi->tagihan_id)->first();
              
                    if( !empty($tagihan) && !empty($transaksis) )
                    {
                        if( $transaksis->status != 0 ) {
                            $tmp->delete();
                            DB::commit();
                            continue;
                        }
                        
                        $user =  User::findOrFail($tmp->transaksi->user_id);
                      
                        if($tagihan->apiserver_id == 1)
                        {
                            if( !empty($transaksis->mtrpln) && $transaksis->mtrpln != '-') {
                                $no = $transaksis->mtrpln;
                            }
                          
                            $check = DigiFlazz::cekStatusPembayaran($transaksis->code, $transaksis->sequence_id, $no, $transaksis->order_id);
                            $check = json_decode($check);
                          
                            if( $check->connected = true )
                            {
                                if( strtolower($check->response->data->status) == 'sukses')
                                {
                                    $data = $check->response->data;
                                    
                                    $tmp->delete();
                                    
                                    $transaksis->token  =  $data->sn;
                                    $transaksis->note   =  $data->message;
                                    $transaksis->status = 1;
                                    
                                    $tagihan->status    = 2;
                                    $tagihan->expired   = 0;
                                    
                                     //BONUS REFERRAL
                                    if(!empty($user->referred_by) || $user->referred_by != 0)
                                    {
                                        $dataKomisi_ref       = Setting::settingsBonus(2);
                                        $ref_user             = $user->referred_by;
                                        $getDataRef           = User::where('id',$ref_user)->first();
                                        $sadlo_ref            = $getDataRef->saldo;
                                        $komisi_ref           = $dataKomisi_ref->komisi;
                                        $akumulasi_komisi_ref = intval($sadlo_ref) + intval($komisi_ref);
                                        
                                        $getDataRef->update([
                                                'saldo'=>$akumulasi_komisi_ref
                                            ]);
                
                                        DB::table('mutasis_komisi')
                                             ->insert([
                                                  'user_id'      => $getDataRef->id,
                                                  'from_reff_id' => $user->id,
                                                  'komisi'       => $komisi_ref,
                                                  'jenis_komisi' => 2,
                                                  'note'         => "BONUS TRANSAKSI REFERRAL Trx ".$data->buyer_sku_code,
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
                                    
                                    //Proses Bonus Komisi
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
                                                           'note'         => "BONUS KOMISI TRANSAKSI REFERRAL (".$user->name.", #".$transaksis->id.") | trx ".$data->buyer_sku_code,
                                                           'created_at'   => date('Y-m-d H:i:s'),
                                                           'updated_at'   => date('Y-m-d H:i:s'),
                                                         ]);
                                
                                                 $mutasiRewardReff = new Mutasi();
                                                 $mutasiRewardReff->user_id = $getDataRef->id;
                                                 $mutasiRewardReff->trxid = $transaksis->order_id;
                                                 $mutasiRewardReff->type = 'credit';
                                                 $mutasiRewardReff->nominal = $komisi_ref;
                                                 $mutasiRewardReff->saldo  = $user_ref->saldo;
                                                 $mutasiRewardReff->note  = "BONUS KOMISI TRANSAKSI REFERRAL (".$user->name.", #".$transaksis->id.") | trx ".$data->buyer_sku_code;
                                                 $mutasiRewardReff->save();    
                                             }
                                        }   
                                    }
                                    
                                    if(!empty($tagihan->phone) && $tagihan->phone != '-')
                                    {
                                        if($user->roles[0]->id == $this->personal_role)
                                        {
                                            SMSGateway::send($tagihan->phone, 'BYR '.$tagihan->product_name.' '.$tagihan->no_pelanggan.' A/N '.$tagihan->nama.' Rp. '.$tagihan->jumlah_tagihan.' Adm Rp'.$tagihan->admin.' SUKSES Reff: '.$transaksis->token);
                                        }
                                    }
                                }
                                else
                                {
                                    $tmp->delete();
                                    
                                    $sisaSaldo = $user->saldo + $tagihan->jumlah_bayar;
                                    $user->refresh();
                                    $user->saldo = $sisaSaldo;
                                    
                                    $transaksis->note       = "Pembayaran Tagihan ".$tagihan->product_name.' Gagal';
                                    $transaksis->status     = 2;
                                    
                                    $tagihan->status    = 3;
                                    $tagihan->expired   = 0;
                                    
                                    $mutasi             = new Mutasi();
                                    $mutasi->user_id    = $user->id;
                                    $mutasi->trxid      = $transaksis->id;
                                    $mutasi->type       = 'credit';
                                    $mutasi->nominal    = $tagihan->jumlah_bayar;
                                    $mutasi->saldo      = $sisaSaldo;
                                    $mutasi->note       = 'PEMBAYARAN TAGIHAN '.$tagihan->product_name.' *GAGAL*';
                                    $mutasi->save();
                                    
                                }
                            }
                        }
                        
                        $user->save();
                        $transaksis->save();
                        $tagihan->save();
                    }
                    
                    DB::commit();
                }
                catch(Exception $e)
                {
                    DB::rollback();
                    Log::error($e);
                }
            }
        }
    }
    
    
}

?>