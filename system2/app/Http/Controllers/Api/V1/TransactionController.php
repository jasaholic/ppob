<?php

namespace App\Http\Controllers\Api\V1;

use Auth, Validator, DB, Exception, Log, DigiFlazz;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\AppModel\{
    Transaksi, Tagihan, Setting, BlockPhone, V_pembelianproduk_enterprise, V_pembelianproduk_agen, V_pembelianproduk_personal, Antriantrx, Mutasi, Pembayaranproduk, KomisiRef
};
use App\Jobs\QueuePembelian;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public $personal_role   = 1;
    public $admin_role      = 2;
    public $agen_role       = 3;
    public $enterprise_role = 4;

    public function __construct()
    {
        $this->settings = Setting::first();
    }
    
    public function historyPrabayar(Request $request)
    {
        $user = $request->user();
        
        $transaction = Transaksi::selectRaw('id as trx_id, via, code, produk, total as price, target, mtrpln, note, token, status, saldo_before_trx, saldo_after_trx, created_at, updated_at')
            ->where('user_id', $user->id)
            ->whereNull('tagihan_id');
        
        if( !empty($request->trx_id) ) {
            $transaction->where('id', intval($request->trx_id));
        }
        
        if( !empty($request->date_start) ) {
            $transaction->where('created_at', '>=', $request->date_start);
        }
        
        if( !empty($request->date_end) ) {
            $transaction->where('created_at', '<=', $request->date_end);
        }
        
        $transaction = $transaction->orderBy('id', 'desc')->limit(1000)->get();
        
        return response()->json([
            'success'   => true,
            'data'  => $transaction
            ]);
    }
    
    public function detailPrabayar(Request $request, $trx_id)
    {
        $user = $request->user();
        
        $transaction = Transaksi::selectRaw('id as trx_id, via, code, produk, total as price, target, mtrpln, note, token, status, saldo_before_trx, saldo_after_trx, created_at, updated_at')
            ->where('user_id', $user->id)
            ->where('id', intval($trx_id))
            ->whereNull('tagihan_id')
            ->first();
            
        if( !$transaction ) {
            return response()->json([
                'success'   => false,
                'message'   => 'Transaksi tidak ditemukan'
                ]);
        }
        
        return response()->json([
            'success'   => true,
            'message'   => 'Transaksi ditemukan',
            'data'  => $transaction
            ]);
    }
    
    public function createPrabayar(Request $request)
    {
        $v = Validator::make($request->all(), [
            'code'  => 'required|string',
            'target' => 'required|string',
            'id_pelanggan' => 'nullable',
            'pin'   => 'required'
            ], [
                'code.required' => 'Kode tidak boleh kosong',
                'code.string'   => 'Kode produk tidak valid',
                'target.required'    => 'Nomor tujuan tidak boleh kosong',
                'target.string'  => 'Nomor tujuan tidak valid',
                'pin.required'  => 'PIN tidak boleh kosong',
            ]);
            
        if( $v->fails() ) {
            return response()->json([
                'success'   => false,
                'message'   => $v->errors()->first()
                ]);
        }
        
        if($this->settings->status == 0 || $this->settings->status_server == 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Sistem sedang maintenance'
                ]);
        }
        
        try
        {
            $user = $request->user();
            
            if( $user->status != 1 ) {
                return response()->json([
                        'success'   => false,
                        'message'   => 'Akun Anda telah dinonaktifkan'
                        ]);
            }
            
            if( $this->settings->force_verification == 1 )
            {
                $verification = DB::table('users_validations')
                            ->where('user_id', $user->id)
                            ->first();
            
                if( !$verification )
                {
                    return response()->json([
                        'success'   => false,
                        'message'   => 'Untuk melakukan transaksi ini, akun Anda harus terverifikasi'
                        ]);
                }
                elseif( $verification->status != '1' )
                {
                    return response()->json([
                        'success'   => false,
                        'message'   => 'Verifikasi akun Anda masih dalam proses review. Anda belum dapat melakukan transaksi ini'
                        ]);
                }
            }
            
            $cekTarget      = BlockPhone::where('phone', $request->target)->count();
            $cekPhoneUser   = BlockPhone::where('phone', $user->phone)->count();
            
            if( $cekTarget || $cekPhoneUser ) {
                return response()->json([
                        'success'   => false,
                        'message'   => 'Nomor HP/Nomor tujuan masuk dalam blacklist'
                        ]);
            }
            
            if( $user->pin != $request->pin ) {
                return response()->json([
                        'success'   => false,
                        'message'   => 'PIN salah'
                        ]);
            }
            
            switch( $user->roles[0]->id )
            {
                case $this->agen_role:
                    $product = V_pembelianproduk_agen::selectRaw('produkpembelian_agen.*, pembelianproduks.apiserver_id')
                        ->join('pembelianproduks', 'pembelianproduks.product_id', '=', 'produkpembelian_agen.product_id')
                        ->where('produkpembelian_agen.product_id', $request->code)
                        ->first();
                    break;
                    
                case $this->enterprise_role:
                    $product = V_pembelianproduk_enterprise::selectRaw('produkpembelian_enterprise.*, pembelianproduks.apiserver_id')
                        ->join('pembelianproduks', 'pembelianproduks.product_id', '=', 'produkpembelian_enterprise.product_id')
                        ->where('produkpembelian_enterprise.product_id', $request->code)
                        ->first();
                    break;
                    
                default:
                    $product = V_pembelianproduk_personal::selectRaw('produkpembelian_personal.*, pembelianproduks.apiserver_id')
                        ->join('pembelianproduks', 'pembelianproduks.product_id', '=', 'produkpembelian_personal.product_id')
                        ->where('produkpembelian_personal.product_id', $request->code)
                        ->first();
                    break;
            }
            
            if( !$product ) {
                return response()->json([
                        'success'   => false,
                        'message'   => 'Produk tidak ditemukan'
                        ]);
            }
            
            if( $product->status != 1 ) {
                return response()->json([
                        'success'   => false,
                        'message'   => 'Produk sedang gangguan'
                        ]);
            }
            
            if( $user->saldo < $product->price ) {
                return response()->json([
                        'success'   => false,
                        'message'   => 'Saldo Anda tidak mencukupi untuk melakukan transaksi ini'
                        ]);
            }
            
            $ltx = Antriantrx::where('code', $product->product_id)
                ->where(function($ant) use ($request) {
                    $ant->where('target', $request->target)
                        ->orWhere('mtrpln', $request->id_pelanggan);
                })
                ->where('created_at', '>=', Carbon::now()->subMinutes(5)->toDateTimeString())
                ->count();
            
            if( $ltx > 0 ) {
                return response()->json([
                        'success'   => false,
                        'message'   => 'Mohon tunggu 5 menit sebelum mengulai transaksi yang sama atau cek riwayat transaksi Anda'
                        ]);
            }
            
            DB::beginTransaction();
            
            try
            {
                $oldBalance = $user->saldo;
                $newBalance = $oldBalance - $product->price;
                
                $user->saldo = $newBalance;
                $user->save();
                
                $antrian                  = new Antriantrx();
                $antrian->apiserver_id    = $product->apiserver_id;
                $antrian->code            = $product->product_id;
                $antrian->produk          = $product->product_name;
                $antrian->harga_default   = $product->price_default;
                $antrian->harga_markup    = $product->price_markup;
                $antrian->target          = $request->target;
                $antrian->via             = 'API';
              
                if ( !empty($request->id_pelanggan) ) {
                    $antrian->mtrpln = $request->id_pelanggan;
                }

                $antrian->note    = "Transaksi dalam antrian.";
                $antrian->status  = 0;
                $antrian->pengirim = $request->ip();
                $antrian->user_id = $user->id; 
                $antrian->save();
          
                $mutasi           = new Mutasi();
                $mutasi->user_id  = $user->id;
                $mutasi->type     = 'debit';
                $mutasi->nominal  = $product->price;
                $mutasi->saldo    = $newBalance;
                $mutasi->note     = !empty($request->id_pelanggan) ? 'TRANSAKSI '.$product->product_name.' '.$request->id_pelanggan : 'TRANSAKSI '.$product->product_name.' '.$request->target;
                $mutasi->save();
                
                DB::commit();
                
                usleep(250000);
                
                dispatch_now(new QueuePembelian($product->apiserver_id, $product, null, $product->product_id, $request->target, $request->id_pelanggan, $user, $request->ip(), $antrian->id, $mutasi->id, 'API', 'otomatis'));
                
                for( $i = 1; $i >= 1; $i++ )
                {
                    $trx = Transaksi::select('id')->where('antrian_id', $antrian->id)->first();
                    
                    if( $trx )
                    {
                        return response()->json([
                            'success'   => true,
                            'message'   => 'Transaksi Anda berhasil diantrikan',
                            'data'      => [
                                'trx_id'    => $trx->id
                                ]
                            ]);
                            
                        break;
                    }
                    else
                    {
                        usleep(500000);
                        
                        continue;
                    }
                }
            }
            catch(Exception $e)
            {
                DB::rollBack();
                
                if( $e instanceof \PDOException ) {
                    Log::error($e);
                }
                
                return response()->json([
                        'success'   => false,
                        'message'   => 'Transaksi tidak dapat diproses. Silahkan ulangi'
                        ]);
            }
        }
        catch(Exception $e)
        {
            if( $e instanceof \PDOException ) {
                Log::error($e);
            }
            
            return response()->json([
                    'success'   => false,
                    'message'   => 'Transaksi tidak dapat diproses. Silahkan ulangi'
                    ]);
        }
    }
    
    public function checkPascabayar(Request $request)
    {
        $v = Validator::make($request->all(), [
            'code'  => 'required|string',
            'id_pelanggan' => 'required',
            'phone' => 'required|string',
            'pin'   => 'required'
            ], [
                'code.required' => 'Kode tidak boleh kosong',
                'code.string'   => 'Kode produk tidak valid',
                'phone.required'    => 'Nomor HP tidak boleh kosong',
                'phone.string'  => 'Nomor HP tidak valid',
                'id_pelanggan.required' => 'ID Pelanggan tidak boleh kosong',
                'pin.required'  => 'PIN tidak boleh kosong',
            ]);
            
        if( $v->fails() ) {
            return response()->json([
                'success'   => false,
                'message'   => $v->errors()->first()
                ]);
        }
        
        if($this->settings->status == 0 || $this->settings->status_server == 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Sistem sedang maintenance'
                ]);
        }
        
        try
        {
            $user = $request->user();
            
            if( $user->status != 1 ) {
                return response()->json([
                        'success'   => false,
                        'message'   => 'Akun Anda telah dinonaktifkan'
                        ]);
            }
            
            if( $this->settings->force_verification == 1 )
            {
                $verification = DB::table('users_validations')
                            ->where('user_id', $user->id)
                            ->first();
            
                if( !$verification )
                {
                    return response()->json([
                        'success'   => false,
                        'message'   => 'Untuk melakukan transaksi ini, akun Anda harus terverifikasi'
                        ]);
                }
                elseif( $verification->status != '1' )
                {
                    return response()->json([
                        'success'   => false,
                        'message'   => 'Verifikasi akun Anda masih dalam proses review. Anda belum dapat melakukan transaksi ini'
                        ]);
                }
            }
            
            $cekTarget      = BlockPhone::where('phone', $request->phone)->count();
            $cekPhoneUser   = BlockPhone::where('phone', $user->phone)->count();
            
            if( $cekTarget || $cekPhoneUser ) {
                return response()->json([
                        'success'   => false,
                        'message'   => 'Nomor HP masuk dalam blacklist'
                        ]);
            }
            
            if( $user->pin != $request->pin ) {
                return response()->json([
                        'success'   => false,
                        'message'   => 'PIN salah'
                        ]);
            }
            
            $product = Pembayaranproduk::with('pembayarankategori')->where('code', $request->code)->first();
            
            if( !$product ) {
                return response()->json([
                        'success'   => false,
                        'message'   => 'Produk tidak ditemukan'
                        ]);
            }
            
            if( $product->status != 1 ) {
                return response()->json([
                        'success'   => false,
                        'message'   => 'Produk sedang gangguan'
                        ]);
            }
            
            DB::beginTransaction();
            
            try
            {
                $tagihan = Tagihan::create([
                    'apiserver_id'  => $product->apiserver_id,
                    'code'          => $product->code,
                    'user_id'       => $user->id,
                    'phone'         => $request->phone,
                    'no_pelanggan'  => $request->id_pelanggan,
                    'via'           => 'API',
                    'product_name'   => $product->product_name,
                ]);
                
                $cekTagihan = DigiFlazz::cekTagihan($product->code ,$request->id_pelanggan, $tagihan->id);
                $cekTagihan = json_decode($cekTagihan);
                
                if( $cekTagihan->success !== true ) {
                    DB::rollBack();
                    return response()->json([
                        'success'   => false,
                        'message'   => $cekTagihan->message
                        ]);
                }

                $cekSaldo = DigiFlazz::cekSaldo();
                $cekSaldo = json_decode($cekSaldo);
                
                $kategori = strtoupper($product->pembayarankategori->product_name);
                
                $cekTagihan = $cekTagihan->response->data;
           
                if($kategori != 'PEMBAYARAN HP PASCABAYAR'){
                     $periode = $cekTagihan->desc->detail[0]->periode;
                }
                
                if( $cekSaldo->response->data->deposit < $cekTagihan->price ) {
                    DB::rollBack();
                    return response()->json([
                        'success'   => false,
                        'message'   => 'Sistem pembayaran error, mohon laporkan admin supaya bisa segera ditangani. Terima kasih'
                        ]);
                }
                
                $detail = $cekTagihan->desc->detail;
                $end   = end($detail);
                $jumlahTagihan = count($cekTagihan->desc->detail);
                
                if($jumlahTagihan > 1)
                {
                    $periode  = $detail[0]->periode.' - '.$end->periode;
                }
                else
                {
                     $periode = $detail[0]->periode;
                }
                
                $tagihan->update([
                   'tagihan_id'     => $cekTagihan->ref_id,
                   'no_pelanggan'   => $cekTagihan->customer_no,
                   'nama'           => ucwords($cekTagihan->customer_name),
                   'periode'        => $periode,
                   'jumlah_tagihan' => $cekTagihan->price,
                   'admin'          => $product->price_markup,
                   'jumlah_bayar'   => ($cekTagihan->price + $product->price_markup),
                ]);
                
                DB::commit();
                
                return response()->json([
                        'success'   => true,
                        'message'   => 'Berhasil melakukan pengecekan tagihan',
                        'data'      => Tagihan::selectRaw('id as trx_id, via, code, product_name, no_pelanggan, nama, periode, jumlah_tagihan, admin, jumlah_bayar, status, created_at, updated_at')->where('id', $tagihan->id)->first()
                        ]);
            }
            catch(Exception $e)
            {
                DB::rollBack();
                
                if( $e instanceof \PDOException ) {
                    Log::error($e);
                }
                
                return response()->json([
                        'success'   => false,
                        'message'   => 'Cek pembayaran gagal. Silahkan ulangi ['.__LINE__.']'
                        ]);
            }
        }
        catch(Exception $e)
        {
            if( $e instanceof \PDOException ) {
                Log::error($e);
            }
            
            return response()->json([
                    'success'   => false,
                    'message'   => 'Cek pembayaran gagal. Silahkan ulangi ['.__LINE__.']'
                    ]);
        }
    }
    
    public function payPascabayar(Request $request)
    {
        $v = Validator::make($request->all(), [
            'trx_id'  => 'required|integer',
            'pin'   => 'required'
            ], [
                'trx_id.required' => 'ID transaksi tidak boleh kosong',
                'trx_id.integer'   => 'ID transaksi tidak valid',
                'pin.required'  => 'PIN tidak boleh kosong',
            ]);
            
        if( $v->fails() ) {
            return response()->json([
                'success'   => false,
                'message'   => $v->errors()->first()
                ]);
        }
        
        if($this->settings->status == 0 || $this->settings->status_server == 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Sistem sedang maintenance'
                ]);
        }
        
        try
        {
            $user = $request->user();
            
            if( $user->status != 1 ) {
                return response()->json([
                        'success'   => false,
                        'message'   => 'Akun Anda telah dinonaktifkan'
                        ]);
            }
            
            if( $this->settings->force_verification == 1 )
            {
                $verification = DB::table('users_validations')
                            ->where('user_id', $user->id)
                            ->first();
            
                if( !$verification )
                {
                    return response()->json([
                        'success'   => false,
                        'message'   => 'Untuk melakukan transaksi ini, akun Anda harus terverifikasi'
                        ]);
                }
                elseif( $verification->status != '1' )
                {
                    return response()->json([
                        'success'   => false,
                        'message'   => 'Verifikasi akun Anda masih dalam proses review. Anda belum dapat melakukan transaksi ini'
                        ]);
                }
            }
            
            if( $user->pin != $request->pin ) {
                return response()->json([
                        'success'   => false,
                        'message'   => 'PIN salah'
                        ]);
            }
            
            $tagihan = Tagihan::where('user_id', $user->id)->find($request->trx_id);
            
            if( !$tagihan ) {
                return response()->json([
                        'success'   => false,
                        'message'   => 'Transaksi tidak ditemukan'
                        ]);
            }
            
            if( $tagihan->status != 0 || $tagihan->expired == 0 ) {
                return response()->json([
                        'success'   => false,
                        'message'   => 'Transaksi kadaluarsa atau sudah terbayar'
                        ]);
            }
            
            if( $user->saldo < $tagihan->jumlah_bayar ) {
                return response()->json([
                        'success'   => false,
                        'message'   => 'Saldo Anda tidak mencukupi untuk membayar tagihan ini'
                        ]);
            }
            
            DB::beginTransaction();
            
            try
            {
                $oldBalance = $user->saldo;
                $newBalance = $oldBalance - $tagihan->jumlah_bayar;
                
                $user->refresh();
                $user->saldo = $newBalance;
                $user->save();
                
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
                $bayarSukses->user_id          = $user->id;
                $bayarSukses->via              = 'API';
                $bayarSukses->jenis_transaksi  = 'otomatis';
                $bayarSukses->saldo_before_trx = $oldBalance;
                $bayarSukses->saldo_after_trx  = $newBalance;
                $bayarSukses->save();
                
                $tagihan->status = 1; // status proses
                $tagihan->expired = 1;
                $tagihan->save();
                
                $mutasi          = new Mutasi();
                $mutasi->trxid   = $bayarSukses->id;
                $mutasi->user_id = $user->id;
                $mutasi->type    = 'debit';
                $mutasi->nominal = $tagihan->jumlah_bayar;
                $mutasi->saldo   = $newBalance;
                $mutasi->note    = 'PEMBAYARAN TAGIHAN '.$tagihan->product_name.' '.$tagihan->no_pelanggan;
                $mutasi->save();
                
                $tagihan_id   = $tagihan->id;
                $transaksi_id = $bayarSukses->id;
                $mutasi_id    = $mutasi->id;
                
                if( $user->referred_by != NULL )
                {
                    $dataKomisi_ref       = Setting::settingsBonus(2) ;
                    $ref_user             = $user->referred_by;
                    $getDataRef           = User::where('id',$ref_user)->first();
                    $sadlo_ref            = $getDataRef->saldo;
                
                    $komisi_ref           = $dataKomisi_ref->komisi;
                    $akumulasi_komisi_ref = $sadlo_ref  + $komisi_ref;
            
                    $user_ref =  DB::table('users')
                                ->where('id', $ref_user)
                                ->update([
                                    'saldo'=>$akumulasi_komisi_ref,
                                    ]);

                    DB::table('mutasis_komisi')
                        ->insert([
                            'user_id'      => $getDataRef->id,
                            'from_reff_id' => $user->id,
                            'komisi'       => $komisi_ref,
                            'jenis_komisi' => 2,
                            'note'         => "Trx ".$data->code,
                            'created_at'   => date('Y-m-d H:i:s'),
                            'updated_at'   => date('Y-m-d H:i:s'),
                            ]);

                    $mutasiRewardReff = new Mutasi();
                    $mutasiRewardReff->user_id = $getDataRef->id;
                    $mutasiRewardReff->trxid = $transaksis->order_id;
                    $mutasiRewardReff->type = 'credit';
                    $mutasiRewardReff->nominal = $komisi_ref;
                    $mutasiRewardReff->saldo  = $user_ref->saldo;
                    $mutasiRewardReff->note  = "BONUS TRANSAKSI REFERRAL (".$user->name.", #".$bayarSukses->id.")";
                    $mutasiRewardReff->save();
                }

                $product = Pembayaranproduk::where('code', $tagihan->code)->first();
                
                $bayartagihan = DigiFlazz::BayarTagihan($tagihan->tagihan_id, $product->code, $bayarSukses->sequence_id, $tagihan->no_pelanggan);
                
                $bayartagihan = json_decode($bayartagihan);
                
                if( $bayartagihan->success != true ) {
                    throw new \Exception($bayartagihan->message, 1);
                }
                
                $bayartagihan = $bayartagihan->response->data;
                $detail = $bayartagihan->desc->detail;
    
                $jumlahTagihan = count($bayartagihan->desc->detail);
                
                $bayarSukses->order_id         = $bayartagihan->ref_id;
                $bayarSukses->code             = $bayartagihan->buyer_sku_code;
                $bayarSukses->produk           = $product->product_name;
                $bayarSukses->target           = $tagihan->phone;
                $bayarSukses->mtrpln           = $bayartagihan->customer_no;
                $bayarSukses->note             = $bayartagihan->message;
                $bayarSukses->save();
            
                DB::commit();
                
                return response()->json([
                        'success'   => true,
                        'message'   => 'Transaksi berhasil diproses'
                        ]);
            }
            catch(Exception $e)
            {
                DB::rollBack();
                
                if( $e instanceof \PDOException ) {
                    Log::error($e);
                }
                
                if( $e->getCode() == '1' ) { // response error dari API
                    return redirect()->back()->with('alert-error', $e->getMessage());
                }
                
                return response()->json([
                        'success'   => false,
                        'message'   => 'Transaksi tidak dapat diproses. Silahkan ulangi'
                        ]);
            }
        }
        catch(Exception $e)
        {
            if( $e instanceof \PDOException ) {
                Log::error($e);
            }
            
            return response()->json([
                    'success'   => false,
                    'message'   => 'Transaksi tidak dapat diproses. Silahkan ulangi'
                    ]);
        }
    }
    
    
    public function historyPascabayar(Request $request)
    {
        $user = $request->user();
        
        $transaction = Tagihan::selectRaw('tagihans.id as trx_id, tagihans.via, tagihans.code, tagihans.product_name, tagihans.no_pelanggan, tagihans.nama, tagihans.periode, tagihans.jumlah_tagihan, tagihans.admin, tagihans.jumlah_bayar, tagihans.status, transaksis.token, tagihans.created_at, tagihans.updated_at')
            ->leftJoin('transaksis', 'tagihans.tagihan_id', '=', 'transaksis.tagihan_id')
            ->where('tagihans.user_id', $user->id)
            ->whereNotNull('tagihans.tagihan_id');
        
        if( !empty($request->trx_id) ) {
            $transaction->where('tagihans.id', intval($request->trx_id));
        }
        
        if( !empty($request->date_start) ) {
            $transaction->where('tagihans.created_at', '>=', $request->date_start);
        }
        
        if( !empty($request->date_end) ) {
            $transaction->where('tagihans.created_at', '<=', $request->date_end);
        }
        
        $transaction = $transaction->orderBy('tagihans.id', 'desc')->limit(1000)->get();
        
        return response()->json([
            'success'   => true,
            'data'  => $transaction
            ]);
    }
    
    public function detailPascabayar(Request $request, $trx_id)
    {
        $user = $request->user();
        
        $transaction = Tagihan::selectRaw('tagihans.id as trx_id, tagihans.via, tagihans.code, tagihans.product_name, tagihans.no_pelanggan, tagihans.nama, tagihans.periode, tagihans.jumlah_tagihan, tagihans.admin, tagihans.jumlah_bayar, tagihans.status, transaksis.token, tagihans.created_at, tagihans.updated_at')
            ->leftJoin('transaksis', 'tagihans.tagihan_id', '=', 'transaksis.tagihan_id')
            ->where('tagihans.user_id', $user->id)
            ->where('tagihans.id', intval($trx_id))
            ->whereNotNull('tagihans.tagihan_id')
            ->first();
            
        if( !$transaction ) {
            return response()->json([
                'success'   => false,
                'message'   => 'Transaksi tidak ditemukan'
                ]);
        }
        
        return response()->json([
            'success'   => true,
            'message'   => 'Transaksi ditemukan',
            'data'  => $transaction
            ]);
    }
}