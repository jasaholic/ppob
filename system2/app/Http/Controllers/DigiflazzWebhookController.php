<?php

namespace App\Http\Controllers;

use DB, Exception, Log;
use Illuminate\Http\Request;
use App\User;
use App\AppModel\Transaksi;
use App\AppModel\Temptransaksi;
use App\AppModel\Setting;
use App\AppModel\Mutasi;
use App\AppModel\SMSGateway;
use App\AppModel\SMSGatewaySetting;
use App\AppModel\Komisiref;
use App\AppModel\Tagihan;
use App\AppModel\Apiserver;
class DigiflazzWebhookController extends Controller
{
    private $secret          = '';
    private $personal_role   = 1;
    private $admin_role      = 2;
    private $agen_role       = 3;
    private $enterprise_role = 4;
    
    
    public function __construct() {
        $this->secret = Apiserver::where('id',1)->first()->api_secret;
    }
    
    public function listen(Request $request)
    {
        DB::beginTransaction();
        
        try
        {
            if( empty($request->header('X-Hub-Signature')) ) {
                throw new Exception("Undefined Signature");
            }
            
            $content = $request->getContent();

            $sign = hash_hmac('sha1', $content, $this->secret);

            if( !hash_equals('sha1='.$sign, $request->header('X-Hub-Signature')) ) {
                throw new Exception("Invalid Signature");
            }
           
            $content = json_decode($content);
         
            $response = [
                'trx_id'        => 0,
                'product_code'  => NULL,
                'status'        => 0,
                'sn'            => NULL,
                'customer_id'   => NULL,
                'phone'         => NULL,
                'note'          => 'Error [#1]',
            ];
            
            if( $request->header('X-Digiflazz-Event') == 'update' )
            {
                $prabayar     = false;
                
                if( preg_match('/DigiFlazz\-Hookshot/i', $request->server('HTTP_USER_AGENT')) ) {
                    $prabayar = true;
                }
                
                if( $prabayar )
                {
                    sleep(2);
                    $this->prabayar($request, $content, $response);
                }
                else
                {
                    sleep(2);
                    $this->pascabayar($request, $content, $response);
                }
            }
        }
        catch(Exception $e)
        {
            DB::rollBack();
            
            Log::error($e);
        }
    }
    
    private function prabayar(Request $request, $content, $response)
    {
        try
        {
            $data           = $content->data;
            $status         = strtolower($data->status);
            
            $customer_no    = explode('.', $data->customer_no);
            
            if( count($customer_no) > 1 )
            {
                if( preg_match('/^[\d]+$/i', $customer_no[0]) ) // if sequence detected
                {
                    $sl             = strlen($customer_no[0]) + 1;
                    $customer_no    = substr($data->customer_no, $sl);
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
            
            $is_pln = strtoupper(substr($data->buyer_sku_code, 0, 3)) == 'PLN' ? true : false;
            
            
            $response = [
                'trx_id'        => $data->ref_id,
                'product_code'  => $data->buyer_sku_code,
                'status'        => $status == 'sukses' ? 1 : ($status == 'gagal' ? 2 : 0),
                'sn'            => isset($data->sn) ? $data->sn : "",
                'customer_id'   => $is_pln ? $customer_no : NULL,
                'phone'         => $is_pln ? NULL : $customer_no,
                'note'          => $data->message
            ];
          
            $transaksi = Transaksi::whereNull('tagihan_id')->where('order_id', $response['trx_id'])->where('status', 0)->first();
          
            if( !$transaksi ) {
                return false;
            }
            
            if( $transaksi->sequence_id > 1 )
            {
                if( $data->customer_no != $transaksi->sequence_id.'.'.($is_pln ? $transaksi->mtrpln : $transaksi->target) ) {
                    return false;
                }
            }
            
            $user           = User::findOrFail($transaksi->user_id);
            $hargaproduk    = $transaksi->total;
            $is_aigo        = preg_match('/^(aim|aigo|axo)[0-9]+$/i', $response['product_code']) ? true : false;
            $is_smartfren   = preg_match('/^(smdv)[0-9]+$/i', $response['product_code']) ? true : false;
            
            $addmessage = "";
            
            if( in_array($response['product_code'], ['AD15', 'AD16', 'AD17', 'AD18', 'AD19']) )
            {
                $addmessage = "Aktivasi paket Axis OWSEM ketik *838*".$response['sn']."#";
            }
            elseif( $is_smartfren )
            {
                $addmessage = "Aktivasi ketik *999*".$response['sn']."#";
            }
            
            if( $response['status'] == 1 ) // SUKSES
            {
                Temptransaksi::where('transaksi_id', $transaksi->id)->delete();
                
                if( $transaksi->status == 0 && $transaksi->jenis_transaksi == 'otomatis')
                {
                    if( $is_pln )
                    {
                        if( $user->roles[0]->id == $this->personal_role )
                        {
                            SMSGateway::send($transaksi->target, 'Pembelian '.$transaksi->produk.' ke '.$response['customer_id'].' sukses. SN : '.$response['sn']);
                        }
                    }
    
                    $transaksi->token   = $response['sn'];
                    $transaksi->note    = $is_pln ? "Trx ".$response['product_code']." ".$response['customer_id']." Sukses. SN : ".$response['sn'] : "Trx ".$response['product_code']." ".$response['phone']." Sukses. SN : ".$response['sn']." ".$addmessage;
                    $transaksi->status  = 1;
                    $transaksi->callback_sent = 0;
                    
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
                                  'note'         => "BONUS TRANSAKSI REFERRAL Trx ".$response['product_code'],
                                  'created_at'   => date('Y-m-d H:i:s'),
                                  'updated_at'   => date('Y-m-d H:i:s'),
                                ]);

                        $mutasiRewardReff = new Mutasi();
                        $mutasiRewardReff->user_id = $getDataRef->id;
                        $mutasiRewardReff->trxid = $transaksi->id;
                        $mutasiRewardReff->type = 'credit';
                        $mutasiRewardReff->nominal = $komisi_ref;
                        $mutasiRewardReff->saldo  = intval($sadlo_ref) + intval($komisi_ref);
                        $mutasiRewardReff->note  = "BONUS TRANSAKSI REFERRAL (".$user->name.", #".$transaksi->id.")";
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
                        
                                $komisi_ref           = $transaksi->harga_markup;
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
                                    'note'         => "BONUS KOMISI TRANSAKSI REFERRAL (".$user->name.", #".$transaksi->id.") | trx ".$response['product_code'],
                                    'created_at'   => date('Y-m-d H:i:s'),
                                    'updated_at'   => date('Y-m-d H:i:s'),
                                ]);
    
                                
                                $mutasiRewardReff = new Mutasi();
                                $mutasiRewardReff->user_id = $getDataRef->id;
                                $mutasiRewardReff->trxid = $transaksi->order_id;
                                $mutasiRewardReff->type = 'credit';
                                $mutasiRewardReff->nominal = $komisi_ref;
                                $mutasiRewardReff->saldo  = $user_ref->saldo;
                                $mutasiRewardReff->note  = "BONUS KOMISI TRANSAKSI REFERRAL (".$user->name.", #".$transaksi->id.") | trx ".$response['product_code'];
                                $mutasiRewardReff->save();   
                            }
                        }
                    }
                    
                    $msg = $is_pln ? "Trx ".$response['product_code']." ".$response['customer_id']." *SUKSES*. SN : ".$response['sn'] : "Trx ".$response['product_code']." ".$response['phone']." *SUKSES*. SN : ".$response['sn'];
                    
                }
            }
            elseif( $response['status'] == 2 ) // GAGAL
            {
                Temptransaksi::where('transaksi_id', $transaksi->id)->delete();
                
                if( $transaksi->status == 0 )
                {
                    if( preg_match('/(saldo|sedang.*gangguan)/i', $response['note']) )
                    {
                        $note = "Produk sedang gangguan";
                    }
                    else
                    {
                        $note = $response['note'];
                    }
                    
                    $sisaSaldo                  = $user->saldo + $hargaproduk;
                    $user->refresh();
                    $user->saldo                = $sisaSaldo;
                    $transaksi->note            = $note.". Saldo dikembalikan";
                    $transaksi->saldo_after_trx = $transaksi->saldo_before_trx;
                    $transaksi->status          = 2;
                    $transaksi->callback_sent   = 0;
    
                    $mutasi          = new Mutasi();
                    $mutasi->user_id = $user->id;
                    $mutasi->trxid   = $transaksi->id;
                    $mutasi->type    = 'credit';
                    $mutasi->nominal = $hargaproduk;
                    $mutasi->saldo   = $sisaSaldo;
                    $mutasi->note    = $is_pln ? 'TRANSAKSI '.$response['product_code'].' '.$response['customer_id'].' GAGAL' : 'TRANSAKSI '.$response['product_code'].' '.$response['phone'].' GAGAL';
                    $mutasi->save();
                    
                    $msg = ($is_pln ? 'TRANSAKSI '.$response['product_code'].' '.$response['customer_id'].' *GAGAL*' : 'TRANSAKSI '.$response['product_code'].' '.$response['phone'].' *GAGAL*').' '.$transaksi->note;
                }
            }
          
            $user->save();
            $transaksi->save();
            
            DB::commit();
          
            $cost_smsbuyer = SMSGatewaySetting::where('name','sms_buyer_cost')->first();
                
            if( $response['status'] == 1 )
            {
                $enable_sms = SMSGatewaySetting::where('name','enable')->first();
                $enable_smsbuyer = SMSGatewaySetting::where('name','enable_sms_buyer')->first();
                $cost_smsbuyer = intval(SMSGatewaySetting::where('name','sms_buyer_cost')->first()->value);
                
                if($enable_sms->value == true && $enable_smsbuyer->value == true){
                    if( !empty(trim($user->sms_buyer)) && $user->saldo >= $cost_smsbuyer && !empty($response['phone']) && (in_array(substr($response['phone'], 0, 3), ['+62', '628']) || substr($response['phone'], 0, 2) === '08') )
                    {
                        
                        $oldBalance = $user->saldo;
                        $user->refresh();
                        $user->saldo = $oldBalance - $cost_smsbuyer;
                        $user->save();
                        
                        $mutasi          = new Mutasi();
                        $mutasi->user_id = $user->id;
                        $mutasi->trxid   = $transaksi->id;
                        $mutasi->type    = 'debit';
                        $mutasi->nominal = $cost_smsbuyer;
                        $mutasi->saldo   = $user->saldo;
                        $mutasi->note    = 'SMS Buyer';
                        $mutasi->save();
                        
                        SMSGateway::send($response['phone'], trim($user->sms_buyer));
                    }   
                }
            }
            
            sleep(1);   
        }catch(\Exception $e){
            \Log::error($e);
        }
    }
    
    private function pascabayar(Request $request, $content, $response)
    {
        $data           = $content->data;
        $status         = strtolower($data->status);
        $customer_no    = $data->customer_no;
        
        $response = [
            'trx_id'        => $data->ref_id,
            'product_code'  => $data->buyer_sku_code,
            'status'        => $status == 'sukses' ? 1 : ($status == 'gagal' ? 2 : 0),
            'sn'            => isset($data->sn) ? $data->sn : "",
            'customer_id'   => $customer_no,
            'phone'         => $customer_no,
            'note'          => $data->message
        ];
        
        $is_pln = strtoupper(substr($data->buyer_sku_code, 0, 3)) == 'PLN' ? true : false;
        $transaksi = Transaksi::whereNotNull('tagihan_id')->where('order_id', $response['trx_id'])->where('status', 0)->first();
      
        if( !$transaksi ) {
            return;
            throw new Exception("Unknown transaction");
        }
        
        $tagihan = Tagihan::where('tagihan_id', $transaksi->tagihan_id)->where('status', 1)->first();
        if( !$tagihan ) {
            return;
            throw new Exception("Unknown transaction");
        }
        
        $user           = User::findOrFail($transaksi->user_id);
        $hargaproduk    = $tagihan->jumlah_bayar;
        
        if( $response['status'] == 1 ) // SUKSES
        {
            Temptransaksi::where('transaksi_id', $transaksi->id)->delete();
            
            if( $transaksi->status == 0 && $transaksi->jenis_transaksi == 'otomatis')
            {
                $transaksi->token   = $response['sn'];
                $transaksi->note    = "Bayar ".$transaksi->produk." ".$response['customer_id']." Sukses. SN/Ref : ".$response['sn'];
                $transaksi->status  = 1;
                $transaksi->callback_sent = 0;
                
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
                              'note'         => "BONUS TRANSAKSI REFERRAL Trx ".$response['product_code'],
                              'created_at'   => date('Y-m-d H:i:s'),
                              'updated_at'   => date('Y-m-d H:i:s'),
                            ]);

                    $mutasiRewardReff = new Mutasi();
                    $mutasiRewardReff->user_id = $getDataRef->id;
                    $mutasiRewardReff->trxid = $transaksis->id;
                    $mutasiRewardReff->type = 'credit';
                    $mutasiRewardReff->nominal = $komisi_ref;
                    $mutasiRewardReff->saldo  = intval($sadlo_ref) + intval($komisi_ref);
                    $mutasiRewardReff->note  = "BONUS TRANSAKSI REFERRAL (".$user->name.", #".$transaksi->id.")";
                    $mutasiRewardReff->save();
                }
                
                // PROSES KOMISI
                 if($user->roles[0]->id == $this->agen_role){
                    if($user->referred_by != NULL)
                    {
                        $ref_user             = $user->referred_by;
                        $getDataRef           = User::where('id',$ref_user)->first();
                        
                        if($getDataRef->roles[0]->id == $this->enterprise_role){
                            $sadlo_ref            = $getDataRef->saldo;
                    
                            $komisi_ref           = $transaksi->harga_markup;
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
                                'note'         => "BONUS KOMISI TRANSAKSI REFERRAL (".$user->name.", #".$transaksi->id.") | trx ".$response['product_code'],
                                'created_at'   => date('Y-m-d H:i:s'),
                                'updated_at'   => date('Y-m-d H:i:s'),
                            ]);

                            
                            $mutasiRewardReff = new Mutasi();
                            $mutasiRewardReff->user_id = $getDataRef->id;
                            $mutasiRewardReff->trxid = $transaksi->order_id;
                            $mutasiRewardReff->type = 'credit';
                            $mutasiRewardReff->nominal = $komisi_ref;
                            $mutasiRewardReff->saldo  = $user_ref->saldo;
                            $mutasiRewardReff->note  = "BONUS KOMISI TRANSAKSI REFERRAL (".$user->name.", #".$transaksi->id.") | trx ".$response['product_code'];
                            $mutasiRewardReff->save();   
                        }
                    }
                }
                
                $msg = $is_pln ? "Trx ".$response['product_code']." ".$response['customer_id']." *SUKSES*. SN : ".$response['sn'] : "Trx ".$response['product_code']." ".$response['phone']." *SUKSES*. SN : ".$response['sn'];
               
            }
        }
        elseif( $response['status'] == 2 ) // GAGAL
        {
            Temptransaksi::where('transaksi_id', $transaksi->id)->delete();
            
            if( $transaksi->status == 0 )
            {
                if( preg_match('/(saldo|sedang.*gangguan)/i', $response['note']) )
                {
                    $note = "Produk sedang gangguan";
                }
                else
                {
                    $note = $response['note'];
                }
                
                $sisaSaldo                  = $user->saldo + $hargaproduk;
                $user->refresh();
                $user->saldo                = $sisaSaldo;
                
                $transaksi->note            = $note.". Saldo dikembalikan";
                $transaksi->saldo_after_trx = $transaksi->saldo_before_trx;
                $transaksi->status          = 2;
                $transaksi->callback_sent   = 0;
                
                $tagihan->status    = 3;
                $tagihan->expired   = 0;

                $mutasi          = new Mutasi();
                $mutasi->user_id = $user->id;
                $mutasi->trxid   = $transaksi->id;
                $mutasi->type    = 'credit';
                $mutasi->nominal = $hargaproduk;
                $mutasi->saldo   = $sisaSaldo;
                $mutasi->note    = "Bayar ".$transaksi->produk." ".$response['customer_id']." ".$response['customer_id']." GAGAL";
                $mutasi->save();
                
                $msg = ($is_pln ? 'TRANSAKSI '.$response['product_code'].' '.$response['customer_id'].' *GAGAL*' : 'TRANSAKSI '.$response['product_code'].' '.$response['phone'].' *GAGAL*').' '.$transaksi->note.' .';
            }
        }
      
        $user->save();
        $transaksi->save();
        $tagihan->save();
        
        DB::commit();
       
        if( $response['status'] == 1 )
        {
            $enable_sms = SMSGatewaySetting::where('name','enable')->first();
            $enable_smsbuyer = SMSGatewaySetting::where('name','enable_sms_buyer')->first();
            
            if($enable_sms->value == true && $enable_smsbuyer->value == true){
                $cost_smsbuyer = intval(SMSGatewaySetting::where('name','sms_buyer_cost')->first()->value);
                if( !empty(trim($user->sms_buyer)) && $user->saldo >= $cost_smsbuyer && !empty($transaksi->target) && (in_array(substr($transaksi->target, 0, 3), ['+62', '628']) || substr($transaksi->target, 0, 2) === '08') )
                {
                    $oldBalance = $user->saldo;
                    $user->refresh();
                    $user->saldo = $oldBalance - $cost_smsbuyer;
                    $user->save();
                    
                    $mutasi          = new Mutasi();
                    $mutasi->user_id = $user->id;
                    $mutasi->trxid   = $transaksi->id;
                    $mutasi->type    = 'debit';
                    $mutasi->nominal = $cost_smsbuyer;
                    $mutasi->saldo   = $user->saldo;
                    $mutasi->note    = 'SMS Buyer';
                    $mutasi->save();
                    
                    SMSGateway::send($transaksi->target, trim($user->sms_buyer));
                }   
            }
        }
        sleep(1);
    }
    
}

