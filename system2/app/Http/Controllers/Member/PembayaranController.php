<?php

namespace App\Http\Controllers\Member;

use DB, Auth, Response, Validator,DigiFlazz,Log;
use App\AppModel\Pembayarankategori;
use App\AppModel\Pembayaranoperator;
use App\AppModel\Pembayaranproduk;
use App\AppModel\Antriantrx;
use App\AppModel\BlockPhone;
use App\AppModel\Transaksi;
use App\AppModel\Temptransaksi;
use App\AppModel\Mutasi;
use App\AppModel\Tagihan;
use App\AppModel\Setting;
use App\AppModel\SMSGateway;
use App\AppModel\Komisiref;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Exception;

class PembayaranController extends Controller
{
    //role user
    public $personal_role   = 1;
    public $admin_role      = 2;
    public $agen_role       = 3;
    public $enterprise_role = 4;
    
    public function __construct()
    {
        $this->settings = Setting::first();
    }
    
    public function index(Request $request, $slug)
    {
        $kategori   = Pembayarankategori::where('slug', $slug)->firstOrFail();
        $operator   = Pembayaranoperator::where('pembayarankategori_id', $kategori->id)->firstOrFail();
        $produk     = Pembayaranproduk::where('pembayarankategori_id', $kategori->id)->where('pembayaranoperator_id', $operator->id)->get();
        $antrian    = Antriantrx::where('user_id', Auth::user()->id)->orderBy('created_at', 'DESC')->Paginate(50);
        $transaksi  = Transaksi::where('user_id', Auth::user()->id)->orderBy('created_at', 'DESC')->paginate(3);
        if($kategori->status == 1)
        {
        	return view('member.pembayaran.form', compact('kategori', 'operator', 'antrian', 'transaksi','produk'));
        }
        else
        {
        	return redirect()->back()->with('alert-error', 'Halaman tidak dapat diakses, produk ini masih dalam pengembangan.');
        }
    }

    public function getTypeheadTagihan(Request $request)
    {
        $data        = $request->q;
        $suggestions = Tagihan::select('no_pelanggan')->where('user_id', Auth::user()->id)->where('no_pelanggan', 'LIKE',"%{$data}%")->orderBy('created_at', 'DESC')->limit(10)->get();
        
        $output = array();
        foreach ($suggestions as $key ) {
            $output[] =  $key->no_pelanggan;
        }
        return json_encode($output);
    }
    
    public function findproductpembayaran(Request $request)
    {
        $produk = Pembayaranproduk::where('pembayarankategori_id', $request->pembayarankategori_id)->where('pembayaranoperator_id', $request->pembayaranoperator_id)->get();
        return Response::json($produk);
    }

    public function cektagihan(Request $request)
    {
        $produk = $request->input('produk');
       
        if($this->settings->status == 0 and $this->settings->status_server == 0) {
            return redirect()->back()->with('alert-error', 'Sistem Sedang Maintenance, mohon kesabarannya menunggu.');
        }
        
        if( $this->settings->force_verification == 1 )
        {
            $verification = DB::table('users_validations')
                        ->select('*')
                        ->where('user_id', Auth::id())
                        ->first();
        
            if( !$verification )
            {
                return redirect()->back()->with('alert-error', 'Untuk melakukan transaksi, akun Anda harus terverifikasi, silahkan lakukan verifikasi <a href="/member/validasi-users" style="font-weight:bold;text-decoration:underline;">DISINI</a> .');
            }
            elseif( $verification->status != '1' )
            {
                return redirect()->back()->with('alert-error', 'Mohon maaf, verifikasi akun Anda masih dalam proses review. Anda belum dapat melakukan transaksi ini');
            }
        }
        
        $this->validate($request,[
            'produk'         => 'required',
            'nomor_rekening' => 'required',
            'target'         => 'required',
            'pin'            => 'required',
        ],[
            'produk.required'         => 'Produk tidak boleh kosong',
            'nomor_rekening.required' => 'Nomor Pelanggan tidak boleh kosong',
            'target.required'         => 'Nomor HP Pembeli tidak boleh kosong',
            'pin.required'            => 'PIN tidak boleh kosong',
        ]);

        $produk       = $request->produk;
        $no_pelanggan = $request->nomor_rekening;
        $phone        = $request->target;
        $pin          = $request->pin;

        $userCek      = User::where('id', Auth::user()->id)->first();
        $cekTarget    = BlockPhone::where('phone', $phone)->first();
        $cekPhoneUser = BlockPhone::where('phone', $userCek->phone)->first();
        
        if( !is_null($cekTarget) || !is_null($cekPhoneUser) )
        {
            return redirect()->back()->with('alert-error', 'No.Target termasuk nomor yang tercatat dalam daftar Blacklist Kami.');
        }
        elseif($userCek->status == 0)
        {
            return redirect()->back()->with('alert-error', 'Maaf, Akun anda di nonaktifkan!');
        }
        elseif( $userCek->pin != $request->pin )
        {
            return redirect()->back()->with('alert-error', 'Maaf, Pin anda salah!');
        }
        
        $getPembayaranData = Pembayaranproduk::with('pembayarankategori')->where('code', $produk)->first();
        
        $cektagihanLokal = Tagihan::where(['no_pelanggan'=>$no_pelanggan,'code'=>$getPembayaranData->code,'product_name'=>$getPembayaranData->product_name])
                            ->where('status',0)->first();
        if($cektagihanLokal){
            return redirect()->back()->with('alert-error','Anda sudah melakukan pengecekan tagihan dengan No.pelanggan '.$no_pelanggan.' ('.ucwords($cektagihanLokal->nama).')');
        }
        DB::beginTransaction();
        
        try
        {
           
            
            if( !$getPembayaranData ) {
                return redirect()->back()->with('alert-error', 'Maaf, produk sedang gangguan');
            }
         
            $tagihan = Tagihan::create([
                    'apiserver_id'  => $getPembayaranData->apiserver_id,
                    'code'          => $getPembayaranData->code,
                    'user_id'       => $userCek->id,
                    'phone'         => $phone,
                    'no_pelanggan'  => $no_pelanggan,
                    'via'           => 'DIRECT',
                    'product_name'   => $getPembayaranData->product_name,
                ]);
            
            $cekTagihan = DigiFlazz::cekTagihan($getPembayaranData->code ,$no_pelanggan, $tagihan->id);
            $cekTagihan = json_decode($cekTagihan);
           
            $cekSaldo = DigiFlazz::cekSaldo();
            $cekSaldo = json_decode($cekSaldo);
          
            $kategori = strtoupper($getPembayaranData->pembayarankategori->product_name);
            
            if( $cekTagihan->success == true )
            {
                $cekTagihan = $cekTagihan->response->data;
               
                if($kategori != 'PEMBAYARAN HP PASCABAYAR'){
                     $periode = $cekTagihan->desc->detail[0]->periode;
                }
                
                if($cekSaldo->response->data->deposit >= $cekTagihan->price)
                {
                    $detail = isset($cekTagihan->desc->detail) ? $cekTagihan->desc->detail : null;
                 
                    if( !is_null($detail) )
                    {
                        $end   = end($detail);
    
                        $jumlahTagihan = count($detail);
                        
                        if($jumlahTagihan > 1)
                        {
                            $periode  = $detail[0]->periode.' - '.$end->periode;
                        }
                        else
                        {
                             $periode = $detail[0]->periode;
                        }
                    }
                    else
                    {
                        $jumlahTagihan = 1;
                        $periode = '';
                    }
                    
                    $admin = $getPembayaranData->price_markup * $jumlahTagihan;

                    $tagihan->update([
                       'tagihan_id'     => $cekTagihan->ref_id,
                       'no_pelanggan'   => $cekTagihan->customer_no,
                       'nama'           => ucwords($cekTagihan->customer_name),
                       'periode'        => $periode,
                       'jumlah_tagihan' => $cekTagihan->price,
                       'admin'          => $getPembayaranData->price_markup,
                       'jumlah_bayar'   => ($cekTagihan->price + $getPembayaranData->price_markup),
                    ]);
                    
                }
                else
                {
                    return redirect()->back()->with('alert-error','Sistem Pembayaran Error, mohon laporkan admin supaya bisa segera ditangani.Terima kasih');
                }
            }
            else
            {
                return redirect()->back()->with('alert-error', $cekTagihan->message);
            }
            
            DB::commit();
            
            $request->session()->regenerateToken();
            
            return redirect()->to('/member/tagihan-pembayaran/'.$tagihan->id);
        }
        catch(\Exception $e)
        {
            DB::rollback();
            Log::error($e);
            if( $e->getCode() == '1' ) {
                return redirect()->back()->with('alert-error', $e->getMessage());
            }
            
            return redirect()->back()->with('alert-error', 'Cek pembayaran gagal, silahkan coba kembali [err-back]');
        }
    }
    
    public function cektagihanhome(Request $request)
    {
        $produk = $request->input('produk');
       
        if($this->settings->status == 0 && $this->settings->status_server == 0){
            return Response::json([ 'success' => false, 'message' => 'Sistem Sedang Maintenance, mohon kesabarannya menunggu.']);
        }
        
        if( $this->settings->force_verification == 1 )
        {
            $verification = DB::table('users_validations')
                        ->select('*')
                        ->where('user_id', Auth::id())
                        ->first();
        
            if( !$verification )
            {
                return Response::json([ 'success' => false, 'message' => 'Untuk melakukan transaksi ini, akun Anda harus terverifikasi, silahkan lakukan verifikasi di menu Validasi User']);
            }
            elseif( $verification->status != '1' )
            {
                return Response::json([ 'success' => false, 'message' => 'Mohon maaf, verifikasi akun Anda masih dalam proses review. Anda belum dapat melakukan transaksi ini']);
            }
        }
        
        $this->validate($request,[
            'produk'         => 'required',
            'nomor_rekening' => 'required',
            'target'         => 'required',
            'pin'            => 'required',
        ],[
            'produk.required'         => 'Produk tidak boleh kosong',
            'nomor_rekening.required' => 'Nomor Pelanggan tidak boleh kosong',
            'target.required'         => 'Nomor HP Pembeli tidak boleh kosong',
            'pin.required'            => 'PIN tidak boleh kosong',
        ]);

        $produk       = $request->produk;
        $no_pelanggan = $request->nomor_rekening;
        $phone        = $request->target;
        $pin          = $request->pin;

        $userCek      = User::where('id',Auth::user()->id)->first();
        $cekTarget    = BlockPhone::where('phone', $phone)->first();
        $cekPhoneUser = BlockPhone::where('phone',$userCek->phone)->first();
        if( !is_null($cekTarget) || !is_null($cekPhoneUser) ) {
            return Response::json([ 'success' => false, 'message' => 'No.Target termasuk nomor yang tercatat dalam daftar Blacklist Kami.']);
        }

        if($userCek->status == 0)
        {
            return Response::json([ 'success' => false, 'message' => 'Maaf, Akun anda di nonaktifkan!']);
        }

        if( $userCek->pin != $request->pin )
        {
            return Response::json([ 'success' => false, 'message' => 'Maaf, Pin anda salah!']);
        }
        $getPembayaranData = Pembayaranproduk::with('pembayarankategori')->where('code', $produk)->first();
        $cektagihanLokal = Tagihan::where(['no_pelanggan'=>$no_pelanggan,'code'=>$getPembayaranData->code,'product_name'=>$getPembayaranData->product_name])
                            ->where('status',0)->first();
        if($cektagihanLokal){
            return Response::json(['success'=>false,'message'=>'Anda sudah melakukan pengecekan tagihan dengan No.pelanggan '.$no_pelanggan.' ('.ucwords($cektagihanLokal->nama).')']);
        }
        DB::beginTransaction();
        
        try
        {
            $getPembayaranData = Pembayaranproduk::with('pembayarankategori')->where('code', $produk)->first();
            
            if( !$getPembayaranData ) {
                return Response::json([ 'success' => false, 'message' => 'Maaf, produk sedang gangguan']);
            }
            
            $tagihan = Tagihan::create([
                    'apiserver_id'   => $getPembayaranData->apiserver_id,
                    'code'          => $getPembayaranData->code,
                    'user_id'       =>$userCek->id,
                    'phone'         =>$phone,
                    'no_pelanggan'  =>$no_pelanggan,
                    'via'           =>'DIRECT',
                    'product_name'   => $getPembayaranData->product_name,
                ]);
            
            $cekTagihan = DigiFlazz::cekTagihan($getPembayaranData->code,$no_pelanggan,$tagihan->id);
            $cekTagihan = json_decode($cekTagihan);
           
            $cekSaldo = DigiFlazz::cekSaldo();
            
            $cekSaldo = json_decode($cekSaldo);
            $cekSaldo = $cekSaldo->response->data;
            
            $kategori = strtoupper($getPembayaranData->pembayarankategori->product_name);
            
            if($cekTagihan->success == true)
            {
                 $cekTagihan = $cekTagihan->response->data;
               
                if($kategori != 'PEMBAYARAN HP PASCABAYAR'){
                    $periode = $cekTagihan->desc->detail[0]->periode;
                }
                
                if($cekSaldo->deposit >= $cekTagihan->price){
                   
                    $detail = $cekTagihan->desc->detail;
                 
                    $end = end($detail);

                    $jumlahTagihan = count($cekTagihan->desc->detail);
                    
                    if($jumlahTagihan > 1){
                        $periode = $detail[0]->periode.' - '.$end->periode;
                    }else{
                         $periode = $detail[0]->periode;
                    }
                    
                    $admin = $getPembayaranData->price_markup * $jumlahTagihan;
                    
                    $tagihan->update([
                       'tagihan_id'     => $cekTagihan->ref_id,
                       'no_pelanggan'   => $cekTagihan->customer_no,
                       'nama'           => ucwords($cekTagihan->customer_name),
                       'periode'        => $periode,
                       'jumlah_tagihan' => $cekTagihan->price,
                       'admin'          => $getPembayaranData->price_markup,
                       'jumlah_bayar'   => ($cekTagihan->price + $getPembayaranData->price_markup),
                    ]);
                }else{
                  return Response::json(['success'=>false, 'message'=>'Sistem Pembayaran Erorr, mohon laporkan admin supaya bisa segera ditangani.Terima kasih']);
                }
            }else{
                return Response::json(['success'=>false, 'message'=>'Sistem Pembayaran Erorr, mohon laporkan admin supaya bisa segera ditangani.Terima kasih']);
            }
            
            DB::commit();
            
            $tagihan->token = csrf_token();
            return Response::json($tagihan);
            
        }
        catch (\Exception $e)
        {
            DB::rollback();
            Log::error($e);
            return Response::json([ 'success' => false, 'message' => 'Cek pembayaran gagal, silahkan coba kembali.[err-back]']);
        }
    }
    
    public function bayartagihan(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'order_id'          => 'required',
        ]);
        
        if( $validate->fails() ) {
            return redirect()->back()->with('alert-error', 'Maaf terjadi kesalahan.');
        }

        if($this->settings->status == 0 && $this->settings->status_server == 0) {
            return redirect()->back()->with('alert-error', 'Sistem Sedang Maintenance, mohon kesabarannya menunggu.');
        }
        
        if( $this->settings->force_verification == 1 )
        {
            $verification = DB::table('users_validations')
                        ->select('*')
                        ->where('user_id', Auth::id())
                        ->first();
        
            if( !$verification )
            {
                return redirect()->back()->with('alert-error', 'Untuk melakukan transaksi ini, akun Anda harus terverifikasi, silahkan lakukan verifikasi <a href="/member/validasi-users" style="font-weight:bold;text-decoration:underline;">DISINI</a> .');
            }
            elseif( $verification->status != '1' )
            {
                return redirect()->back()->with('alert-error', 'Mohon maaf, verifikasi akun Anda masih dalam proses review. Anda belum dapat melakukan transaksi ini');
            }
        }
        
        $user = Auth::user();

        $tagihan = Tagihan::where('id', $request->order_id)->where('user_id', $user->id)->first();

        #jika tagihan ini bukan milik user
        if( !$tagihan ) {
            $message = 'Maaf ID Tagihan ini bukan milik Anda.';
            return redirect()->back()->with('alert-error', 'Maaf ID Tagihan ini bukan milik Anda.');
        }
 
        $userCek      = User::where('id', $user->id)->first();
        $cekTarget    = BlockPhone::where('phone', $tagihan->no_pelanggan)->first();
        $cekPhoneUser = BlockPhone::where('phone', $userCek->phone)->first();
        
        if( !is_null($cekTarget) || !is_null($cekPhoneUser) ) {
            return redirect()->back()->with('alert-error', 'No.Target termasuk nomor yang tercatat dalam daftar Blacklist Kami.');
        }

        if( $userCek->saldo <= $tagihan->jumlah_bayar ) { // jika saldo member tidak cukup
            return redirect()->back()->with('alert-error', 'Saldo Anda tidak mencukupi untuk melakukan transaksi ini, TOPUP saldo anda untuk dapat melakukan transaksi');
        }
        sleep(2);
        DB::beginTransaction();
          
        try
        {
            $sisaSaldo = $userCek->saldo - $tagihan->jumlah_bayar;
            $userCek->refresh();
            $userCek->saldo = $sisaSaldo;
            $userCek->save();
            
            $bayarSukses                   = new Transaksi();
            $bayarSukses->apiserver_id     = $tagihan->apiserver_id;                
            $bayarSukses->order_id         = 0;
            $bayarSukses->tagihan_id       = $tagihan->tagihan_id;
            $bayarSukses->code             = "";
            $bayarSukses->produk           = "";
            $bayarSukses->harga_default    = $tagihan->jumlah_tagihan;
            $bayarSukses->harga_markup     = $tagihan->admin;
            $bayarSukses->total            = $tagihan->jumlah_bayar;
            $bayarSukses->target           = "";
            $bayarSukses->mtrpln           = "";
            $bayarSukses->note             = "Initialize";
            $bayarSukses->pengirim         = $request->ip();
            $bayarSukses->status           = 0; // status proses
            $bayarSukses->user_id          = $userCek->id;
            $bayarSukses->via              = 'DIRECT';
            $bayarSukses->jenis_transaksi  = 'otomatis';
            $bayarSukses->saldo_before_trx = $userCek->saldo + $tagihan->jumlah_bayar;
            $bayarSukses->saldo_after_trx  = $userCek->saldo;
            $bayarSukses->save();
            
            $tagihan->status = 1; // status proses
            $tagihan->expired = 1;
            $tagihan->save();
            
            $mutasi          = new Mutasi();
            $mutasi->trxid   = $bayarSukses->id;
            $mutasi->user_id = $userCek->id;
            $mutasi->type    = 'debit';
            $mutasi->nominal = $tagihan->jumlah_bayar;
            $mutasi->saldo   = $userCek->saldo;
            $mutasi->note    = 'PEMBAYARAN TAGIHAN '.$tagihan->product_name.' '.$tagihan->no_pelanggan;
            $mutasi->save();
            
            $tagihan_id   = $tagihan->id;
            $transaksi_id = $bayarSukses->id;
            $mutasi_id    = $mutasi->id;
            
            $product = Pembayaranproduk::where('product_name', $tagihan->product_name)->first();
            
            $bayartagihan = DigiFlazz::BayarTagihan($tagihan->tagihan_id, $product->code, $bayarSukses->sequence_id, $tagihan->no_pelanggan);
            
            $bayartagihan = json_decode($bayartagihan);
            
            if( $bayartagihan->success != true ) {
                throw new \Exception($bayartagihan->message, 1);
            }
            
            $bayartagihan = $bayartagihan->response->data;
            
            $bayarSukses->order_id         = $bayartagihan->ref_id;
            $bayarSukses->code             = $bayartagihan->buyer_sku_code;
            $bayarSukses->produk           = $product->product_name;
            $bayarSukses->target           = $tagihan->phone;
            $bayarSukses->mtrpln           = $bayartagihan->customer_no;
            $bayarSukses->note             = $bayartagihan->message;
            $bayarSukses->save();
            
            //BONUS REFERRAL
            if(!empty($userCek->referred_by) || $userCek->referred_by != 0)
            {
                $dataKomisi_ref       = Setting::settingsBonus(2);
                $ref_user             = $userCek->referred_by;
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
                          'from_reff_id' => $userCek->id,
                          'komisi'       => $komisi_ref,
                          'jenis_komisi' => 2,
                          'note'         => "BONUS TRANSAKSI REFERRAL Trx ".$bayartagihan->buyer_sku_code,
                          'created_at'   => date('Y-m-d H:i:s'),
                          'updated_at'   => date('Y-m-d H:i:s'),
                        ]);

                $mutasiRewardReff = new Mutasi();
                $mutasiRewardReff->user_id = $getDataRef->id;
                $mutasiRewardReff->trxid = $bayarSukses->id;
                $mutasiRewardReff->type = 'credit';
                $mutasiRewardReff->nominal = $komisi_ref;
                $mutasiRewardReff->saldo  = intval($sadlo_ref) + intval($komisi_ref);
                $mutasiRewardReff->note  = "BONUS TRANSAKSI REFERRAL (".$userCek->name.", #".$bayarSukses->id.")";
                $mutasiRewardReff->save();
            }
            
             //PROSES BONUS KOMISI
            if($userCek->roles[0]->id == $this->agen_role){
                if($userCek->referred_by != NULL)
                {
                    $ref_user             = $userCek->referred_by;
                    $getDataRef           = User::where('id',$ref_user)->first();
                    
                    
                    if($getDataRef->roles[0]->id == $this->enterprise_role){
                        $sadlo_ref            = $getDataRef->saldo;
             
                        $komisi_ref           = $bayarSukses->harga_markup;
                        $akumulasi_komisi_ref = $sadlo_ref  + $komisi_ref;
                        
                        $getDataRef->update([
                                'update'=>$akumulasi_komisi_ref
                            ]);
                            
                         DB::table('mutasis_komisi')
                              ->insert([
                                   'user_id'      => $getDataRef->id,
                                   'from_reff_id' => $user->id,
                                   'komisi'       => $komisi_ref,
                                   'jenis_komisi' => 2,
                                   'note'         => "BONUS KOMISI TRANSAKSI REFERRAL (".$userCek->name.", #".$bayarSukses->id.") | trx ".$bayartagihan->buyer_sku_code,
                                   'created_at'   => date('Y-m-d H:i:s'),
                                   'updated_at'   => date('Y-m-d H:i:s'),
                                 ]);
        
                         $mutasiRewardReff = new Mutasi();
                         $mutasiRewardReff->user_id = $getDataRef->id;
                         $mutasiRewardReff->trxid = $bayarSukses->order_id;
                         $mutasiRewardReff->type = 'credit';
                         $mutasiRewardReff->nominal = $komisi_ref;
                         $mutasiRewardReff->saldo  = $getDataRef->saldo;
                         $mutasiRewardReff->note  = "BONUS KOMISI TRANSAKSI REFERRAL (".$userCek->name.", #".$bayarSukses->id.") | trx ".$bayartagihan->buyer_sku_code;
                         $mutasiRewardReff->save();
                    } 
                }   
            }
            
            DB::commit();
            
            $request->session()->regenerateToken();
            
            return redirect()->to('/member/riwayat-transaksi/'.$bayarSukses->id);    
        }
        catch (\Exception $e)
        {
            Log::error($e);
            DB::rollback();
            
            if( $e->getCode() == '1' ) { // response error dari API
                return redirect()->back()->with('alert-error', $e->getMessage());
            }
            
            return redirect()->back()->with('alert-error', 'Please try again Error.[err-back]');
        }
    }
    
}