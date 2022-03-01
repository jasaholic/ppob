<?php

namespace App\Http\Controllers\Member;

use Auth, Response, Validator;
use App\AppModel\Bank;
use App\AppModel\Bank_kategori;
use App\AppModel\Provider;
use App\AppModel\Mutasi;
use App\AppModel\Deposit;
use App\AppModel\Transaksi;
use App\AppModel\Setting;
use App\AppModel\Kurs;
use App\AppModel\MenuSubmenu;
use App\AppModel\VirtualAccountNumber;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\AppModel\SMSGateway;
use App\AppModel\BlockPhone;
use DB;
use Log;
use PaymentTripay;
use App\User;

class DepositController extends Controller
{
    public function __construct()
    {
        $this->settings = Setting::first();
    }
    
    public function index()
    {
        $URL_uri = request()->segment(1).'/'.request()->segment(2);
        $datasubmenu2 = MenuSubmenu::getSubMenuOneMemberURL($URL_uri)->first();

        if($datasubmenu2->status_sub != 0 )
        {   
            $bank = Bank_kategori::with(['bank'=>function($q){
                $q->where('status',1);
            }])->where('status',1)->orderby('urutan','asc')->get();
            
    	   return view('member.deposit.form',compact('bank'));
        }
        else
        {
            abort(404);
        }
    }

    public function bank_cek($id){
        $bank = Bank::where('id',$id)->first();
        if($bank){
            return response()->json(['success'=>true,'is_close'=>$bank->is_closed],200);
        }else{
            return response()->json(['success'=>false,'message'=>'Bank Not found!'],500);
        }
    }
    public function depositsaldo(Request $request)
    {
        $this->validate($request,[
            'bank_id'          => 'required',
            'id_category_bank' => 'required',
            'nominal'          => 'required|regex:/^[0-9\.]+$/i',
        ],[
            'bank_id.required'          => 'Bank boleh kosong',
            'id_category_bank.required' => 'Kategori Bank tidak boleh kosong',
            'nominal.required'          => 'Nominal tidak boleh kosong',
        ]);
        
        if( $this->settings->status == 0 ) {
            return redirect()->back()->with('alert-error', 'Sistem Sedang Maintenance, mohon kesabarannya menunggu.');
        }

        if(($request->input('id_category_bank') == '') || ($request->input('id_category_bank') != '2' && $request->input('bank_id') == '')){
            return redirect()->back()->with('alert-error', 'Pilih terlebih dahulu jenis pembayaran yang ingin anda gunakan.!');
        }
        
        $getbank = Bank::find($request->bank_id);
        if($getbank == null || empty($getbank)){
            return redirect()->back()->withErrors('alert-error','Data Bank tidak Ditemukan');
        }

        $getkategoribank = Bank_kategori::find($getbank->bank_kategori_id);
        if($getkategoribank == null || empty($getkategoribank)){
            return redirect()->back()->with('alert-error','Data Kategori Bank Tidak Ditemukan');
        }

        $provider = Provider::find($getbank->provider_id);
        if($provider == null || empty ($provider)){
            return redirect()->back()->with('alert-error','Data Kategori Bank tidak ditemukan');
        }

        $userCek  = User::where('id',Auth::user()->id)->first();
        if($userCek->status == 0){
            return redirect()->back()->with('alert-error','Maaf Akun anda dinonaktifkan');
        }
        $cekPhone = BlockPhone::getDataPhoneWhere($userCek->phone);
        $rolesId = $userCek->roles()->first()->id;
      
        if( !$cekPhone )
        {
          
            if( $this->settings->force_verification == 1 )
            {
                $verification = DB::table('users_validations')
                            ->select('*')
                            ->where('user_id', auth()->user()->id)
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
                        
            $nominal = str_replace(".", "", $request->nominal);
            
            $nominal_trf = str_replace(".", "", $request->nominal) + intval($this->settings->deposit_fee);
            
            $getData = Deposit::getMinDeposit();
     
            if($getbank->is_closed == 1){
                if( $nominal < $getData[0]->minimal_nominal )
                {
                    return redirect()->back()->with('alert-error', 'Minimal Deposit Rp. '.number_format($getData[0]->minimal_nominal, 0, '.', '.').'');
                }
                elseif( substr($nominal, -3) !== '000' )
                {
                    return redirect()->back()->with('alert-error', 'Deposit harus dengan nominal kelipatan 1000, misal : 50000, 51000, 100000, dst');
                }
                else
                {
                    if(Auth::user()->roles()->first()->id == 1){
                        if( $this->settings->max_daily_deposit_personal > 0 )
                        {
                            $dailyDepositRequest = (int) Deposit::where('user_id', Auth::id())->whereDate('created_at', date('Y-m-d'))->whereIn('status', [0,1])->count();
                            
                            if( $dailyDepositRequest >= $this->settings->max_daily_deposit_personal )
                            {
                                return redirect()->back()->with('alert-error', 'Anda sudah mencapai batas maksimum request deposit harian!');
                            }
                        }
                    }else if(Auth::user()->roles()->first()->id == 3){
                        if( $this->settings->max_daily_deposit_agen > 0 )
                        {
                            $dailyDepositRequest = (int) Deposit::where('user_id', Auth::id())->whereDate('created_at', date('Y-m-d'))->whereIn('status', [0,1])->count();
                            
                            if( $dailyDepositRequest >= $this->settings->max_daily_deposit_agen )
                            {
                                return redirect()->back()->with('alert-error', 'Anda sudah mencapai batas maksimum request deposit harian!');
                            }
                        }
                    }else if(Auth::user()->roles()->first()->id == 4){
                        if( $this->settings->max_daily_deposit_enterprise > 0 )
                        {
                            $dailyDepositRequest = (int) Deposit::where('user_id', Auth::id())->whereDate('created_at', date('Y-m-d'))->whereIn('status', [0,1])->count();
                            
                            if( $dailyDepositRequest >= $this->settings->max_daily_deposit_enterprise )
                            {
                                return redirect()->back()->with('alert-error', 'Anda sudah mencapai batas maksimum request deposit harian!');
                            }
                        }
                    }
                  
                    if($provider->name == 'CekMutasi')
                    {
                         if( (date("G") < 21 && date("G") >= 3) )
                         {
                            
                            $code_unik = mt_rand(1, 999);
                            if( substr($code_unik, -1) == "0" )
                            {
                                $code_unik = $code_unik + mt_rand(1,9);
                            }
                            
                            $nominal_trf = (intval($nominal_trf) + intval($code_unik));
                         
                            for($i=1; $i>=1; $i++)
                            {
                                $check = (int) Deposit::where('nominal_trf', $nominal_trf)->whereDate('created_at', date('Y-m-d'))->whereIn('status', [0,1,3])->count();
                             
                                if( $check <= 0 )
                                {
                                    break;
                                }
                                else
                                {
                                    $code_unik++;
                                    $nominal_trf++;
                                   
                                }
                            }
                            DB::beginTransaction();
                           
                            try{
                                $deposit = new Deposit();
                                $deposit->bank_id = $getbank->id;
                                $deposit->bank_kategori_id = $getbank->bank_kategori_id;
                                $deposit->code_unik = $code_unik;
                                $deposit->nominal = $nominal;
                                $deposit->nominal_trf = $nominal_trf;
                                $deposit->note = "Menunggu pembayaran sebesar Rp ".number_format($nominal_trf, 0, '.', '.');
                                $deposit->user_id = Auth::user()->id;
                                $deposit->save();
                               
                                DB::commit();
                                return redirect()->to('/member/deposit/'.$deposit->id);
                            }catch(\Exception $e){
                                DB::rollback();
                                return redirect()->back()->with('alert-error','Deposit Gagal');
                            }
                        }
                        else
                        {
                            return redirect()->back()->with('alert-error', 'Deposit tidak dapat dilakukan pada pukul 21.00 - 03.00 WIB, silahkan melakukan deposit diluar dari pada jam tersebut.');
                        }
                    }
                    
                    if($provider->name == 'PaymentTripay'){
                        DB::beginTransaction();
                        try{
                            $paymentMethod = $getbank->code;
                           
                            $deposit                   = new Deposit();
                            $deposit->bank_id          = $getbank->id;
                            $deposit->bank_kategori_id = $getbank->bank_kategori_id;
                            $deposit->code_unik        = 0;
                            $deposit->nominal          = $nominal;
                            $deposit->nominal_trf      = $nominal_trf;
                            $deposit->note             = "Menunggu pembayaran sebesar Rp ".number_format($nominal, 0, '.', '.');
                            $deposit->user_id          = $userCek->id;
                            $deposit->save(); 
                            
                            $PaymentTripay = PaymentTripay::trx_close_payment([
                                'method'=>$paymentMethod,
                                'merchant_ref'=>$deposit->id,
                                'amount'=>$nominal_trf,
                                'customer_name'=>$userCek->name,
                                'customer_email'=>$userCek->email,
                                'customer_phone'=>$userCek->phone,
                                'callback_url'=>url('callback'),
                                'return_url'=>url('member/riwayat_deposit'),
                            ]);

                            $result = json_decode($PaymentTripay);
                           
                            if($result->success == true){
                                $deposit->nominal_trf = $result->data->amount;
                                $deposit->payment_url = $result->data->checkout_url;
                                $deposit->save();

                                DB::commit();
                                return redirect($result->data->checkout_url);
                            }else{
                                DB::rollback();
                            }
                             
                        }catch(\Exception $e){
                            \Log::error($e);
                            DB::rollback();
                            return redirect()->back()->with('alert-error', 'Terjadi Kesalahan');
                        }
                    }
                    return redirect()->back()->with('alert-error', 'Gagal Diproses!.');
                }
            }else{
                if($provider->name == 'PaymentTripay'){
                    $cek_nomer_va = VirtualAccountNumber::where('user_id',$userCek->id)->where('bank_id',$getbank->id)->first();
                 
                    if(!$cek_nomer_va){
                        try{
                            $PaymentTripay = PaymentTripay::trx_open_payment([
                                'method'=>$getbank->code,
                                'merchant_ref'=>$userCek->id,
                                'customer_name'=>$userCek->name,
                            ]);
        
                            $result = json_decode($PaymentTripay);
                          
                            if($result->success == true){
                               $va_number = VirtualAccountNumber::create([
                                    'user_id'=>$userCek->id,
                                    'bank_id'=>$getbank->id,
                                    'number_va'=>$result->data->pay_code,
                                    'uuid'=>$result->data->uuid,
                               ]);
    
                               if($va_number){
                                   DB::commit();
                                   return redirect('member/va_number/'.$va_number->id);
                               }else{
                                   DB::rollback();
                                   return redirect()->back()->with('alert-error','Gagal generate nomer VA');
                               }
                            }else{
                                DB::rollback();
                                return redirect()->back()->with('alert-error','Gagal generate nomer VA');
                            }
                        }catch(\Exception $e){
                            \Log::error($e);
                            DB::rollback();
                            return redirect()->back()->with('alert-error','Gagal Diproses!');
                        }
                    }else{
                        DB::rollback();
                        return redirect('/member/va_number/'.$cek_nomer_va->id);
                    }
                    
                }else{
                    return redirect()->back('alert-error','Gagal diproses!.');
                }
            }
           
        }
        else
        {
            return redirect()->back()->with('alert-error', 'Maaf, No.Hp Anda Diblokir!');
        }
    }
    
    public function showNomerVa($id)
    {
        $number_va = VirtualAccountNumber::with('bank')->with('user')->where('id',$id)->first();
        if(!$number_va){
            return abort(404);
        }
        $instruction = [];

        $instruksi = PaymentTripay::instruction([
            'code'=>$number_va->bank->code,
            'pay_code'=>$number_va->number_va,
            ]);
        $instruksi = json_decode($instruksi);
        
        if($instruction->success == true){
            $instruction = $instruksi->data;
        }
        
    }
    public function riwayat_deposit(){
        $deposit = Deposit::where('user_id',auth()->user()->id)->OrderBy('id','desc')->paginate(10);
        return view('member.deposit.riwayat-deposit',compact('deposit'));
    }
    public function showDeposit($id)
    {
        $logo = DB::table('logos')->where('id',3)->first();
        $banks = Bank::all();
        $deposits = Deposit::where('user_id', Auth::user()->id)->findOrFail($id);
        return view('member.deposit.show', compact('deposits', 'banks','logo'));
    }
    
    public function konfirmasiPembayaran(Request $request)
    {   
        
        //try{
            $validator = Validator::make($request->all(),[
                    'bukti' => 'required|image|mimes:jpeg,png,jpg|max:5120',
                ],[
                    'bukti.required'      => 'Bukti pembayaran tidak boleh kosong',
                    'bukti.image'         => 'Bukti pembayaran harus berformat gambar',
                    'bukti.mimes'         => 'Bukti pembayaran harus dalam format png, jpg, atau jpeg',
                    'bukti.max'           => 'Bukti pembayaran Max Size 5MB',
                ]);
            if($validator->fails()){
                return redirect()->back()->with('alert-error',$validator->errors()->first());
            }
           
            $bukti = $request->file('bukti');
            $extension = $bukti->getClientOriginalExtension();
            if(!in_array($extension,['png','jpeg','jpg'])){
                return redirect()->back()->with('alert-error','Format File yang anda upload tidak didukung');
            }
           
            $nameBukti      = 'bukti_'.$request->id.time().'.'.strtolower($bukti->getClientOriginalExtension());
          
            $extension = $bukti->getClientOriginalExtension();
            
            if(!in_array($extension,['png','jpg','jpeg'])){
                return redirect()->back()->with('alert-error','Format Bukti Pembayaran yang anda upload tidak didukung!');
            }
            
            if (!file_exists(public_path('img/validation/deposit/'))) {
                mkdir(public_path('img/validation/deposit'), 0777, true);
            }
            
           
            $destinationIMG      = public_path('img/validation/deposit/');
            $upload_bukti_success  =  $bukti->move($destinationIMG, $nameBukti);
            
                
            $deposit = Deposit::where('user_id', Auth::user()->id)->findOrFail($request->id);
            
            if( $deposit->status == 0 )
            {
                $deposit->status = 3;// status proses
                $deposit->note = 'Pembayaran telah di konfirmasi, proses validasi.';
                $deposit->bukti = $nameBukti;
                $deposit->save();
                
                return redirect()->back();
            }
        // }catch(\Exception $e){
        //     \Log::error($e);
        //     return redirect()->back()->with('alert-error','Terjadi Kesalahan');
        // }
       
    }
    
    public function transferSaldo()
    {
        $URL_uri = request()->segment(1).'/'.request()->segment(2);
        $datasubmenu2 = MenuSubmenu::getSubMenuOneMemberURL($URL_uri)->first();
        $setting = $this->settings;
    
        if($datasubmenu2->status_sub != 0 )
        {
            return view('member.deposit.transfer-saldo.index',compact('setting'));
        }
        else
        {
            abort(404);
        }
    }
    
    public function cekNomor(Request $request)
    {
        $rules = array (
            'no_tujuan' => 'required',
        );
        
        $validator = Validator::make ($request->all(), $rules );
        
        if ($validator->fails ())
        {
            return Response::json ( array (
                    'errors' => $validator->getMessageBag ()->toArray () 
            ) );
        }
        else
        {
            $user = User::where('phone', $request->no_tujuan)->first();
            return Response::json($user);
        }
    }
    
    public function kirimSaldo(Request $request)
    {
        if( $this->settings->status == 0 ) {
            return redirect()->back()->with('alert-error', 'Sistem Sedang Maintenance, mohon kesabarannya menunggu.');
        }
        
        $this->validate($request, [
            'no_tujuan' => 'required',
            'nominal' => 'required',
            'password' => 'required|passcheck:' . Auth::user()->password,
        ],[
            'no_tujuan.required' => 'Nomor Handphone Tujuan Transfer tidak boleh kosong.',
            'nominal.required' => 'Nominal Transfer tidak boleh kosong.',
            'nominal.regex'=>'Nominal yang anda masukkan tidak valid',
            'password.required' => 'Kata Sandi tidak boleh kosong.',
            'password.passcheck' => 'Kata Sandi tidak cocok, periksa kembali kata sandi anda.',
        ]);
        
        if( $this->settings->force_verification == 1 )
        {
            $verification = DB::table('users_validations')
                        ->select('*')
                        ->where('user_id', auth()->user()->id)
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
        
        $nominal = $request->nominal;
        
        if( $request->no_tujuan != Auth::user()->phone )
        {
            $penerima = User::where('phone', $request->no_tujuan)->first();
            
            if( !empty($penerima) )
            {
                $saldo = Auth::user()->saldo;    
        
                $minim_saldo    = $this->settings->min_saldo_user;
                $minim_transfer = $this->settings->min_nominal_transfer;

                if($saldo >= $minim_saldo)
                {
                    if( $nominal >= $minim_transfer )
                    {
                        // Kurang Saldo Pengirim
                        $pengirim = Auth::user();
                       
                        // Tambah Saldo Penerima
                       
                        $sisaSaldoPengirim = $pengirim->saldo - $nominal;
                        $sisaSaldoPenerima = $penerima->saldo + $nominal;
                        $pengirim->saldo = $sisaSaldoPengirim;
                        $penerima->saldo = $sisaSaldoPengirim;
                        $pengirim->save();
                        $penerima->save();
                        
                        // Mutasi Saldo Pengirim
                        $mutasiPengirim = new Mutasi();
                        $mutasiPengirim->user_id = $pengirim->id;
                        $mutasiPengirim->type = 'debit';
                        $mutasiPengirim->nominal = $nominal;
                        $mutasiPengirim->saldo  = $sisaSaldoPengirim;
                        $mutasiPengirim->note  = 'TRANSFER SALDO KE '.$request->no_tujuan.' BERHASIL';
                        $mutasiPengirim->save();
                        
                        // Mutasi Saldo Penerima
                        $mutasiPenerima = new Mutasi();
                        $mutasiPenerima->user_id = $penerima->id;
                        $mutasiPenerima->type = 'credit';
                        $mutasiPenerima->nominal = $nominal;
                        $mutasiPenerima->saldo  = $sisaSaldoPenerima;
                        $mutasiPenerima->note  = 'SALDO TRANSFER DARI '.$pengirim->phone;
                        $mutasiPenerima->save();
  
                        return redirect()->back()->with('alert-success', 'Transfer Saldo Berhasil, Saldo penerima telah di tambahkan.');
                    }
                    else
                    {
                        return redirect()->back()->with('alert-error','Transfer Saldo Gagal, minimal nominal saldo yang anda dapat transfer adalah Rp. '.number_format($minim_transfer,0,'.','.'));
                    }
                }
                else
                {
                    return redirect()->back()->with('alert-error', 'Transfer Saldo Gagal, anda harus memiliki minimal saldo Rp. '.number_format($minim_saldo,0,'.','.').' untuk dapat melakukan transfer saldo.');    
                }
            }
            else
            {
                return redirect()->back()->with('alert-error', 'Nomor Handphone tujuan transfer tidak ditemukan, periksa kembali nomor handphone tujuan anda.');
            }
        }
        else
        {
            return redirect()->back()->with('alert-error', 'Anda tidak dapat melakukan transfer saldo ke akun anda sendiri.');
        }
    }
    
    public function mutasiSaldo()
    {
        $URL_uri = request()->segment(1).'/'.request()->segment(2);
        $datasubmenu2 = MenuSubmenu::getSubMenuOneMemberURL($URL_uri)->first();

        if($datasubmenu2->status_sub != 0 )
        {
            $setting = Setting::first();
            $mutasiWeb = Mutasi::where('user_id', Auth::user()->id)->orderBy('created_at', 'DESC')->get();
            $mutasiMobile = Mutasi::where('user_id', Auth::user()->id)->orderBy('created_at', 'DESC')->paginate(10);
    	    return view('member.deposit.mutasi-saldo.index', compact('mutasiWeb', 'mutasiMobile','setting'));
        }
        else
        {
            abort(404);
        }

    }    

    public function mutasiSaldoDatatables(Request $request)
    {
        $columns = array( 
                            0 =>'no', 
                            1 =>'created_at',
                            2=> 'type',
                            4=> 'nominal',
                            5=> 'saldo',
                            6=> 'trxid',
                            7=> 'note',
                        );
  
        $totalData = Mutasi::where('user_id', Auth::user()->id)->orderBy('created_at', 'DESC')->count();
            
        $totalFiltered = $totalData; 

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir   = $request->input('order.0.dir');


        if(empty($request->input('search.value')))
        {            
            $posts = Mutasi::where('user_id', Auth::user()->id)
                             ->offset($start)
                             ->limit($limit)
                             ->orderBy('created_at', 'DESC')
                             ->get();
        }
        else
        {
            $search = $request->input('search.value'); 

            if(strtoupper($search) == 'DEBET'){
                $type = 'debit';
            }elseif(strtoupper($search) == 'KREDIT'){
                $type = 'credit';
            }else{
                $type = null;
            };
                  
            $posts =  Mutasi::where('user_id', Auth::user()->id)
                        ->where(function($q) use ($search,$type){
                                $q->where('trxid','LIKE',"%{$search}%");
                                $q->orWhere('note','LIKE',"%{$search}%");
                                if($type != null){
                                    $q->orWhere('type', $type);
                                }
                          })
                        ->offset($start)
                        ->limit($limit)
                        // ->orderBy($order,$dir)
                        ->orderBy('created_at', 'DESC')
                        ->get();

             $totalFiltered = Mutasi::where('user_id', Auth::user()->id)
                                ->where(function($q) use ($search,$type){
                                        $q->where('trxid','LIKE',"%{$search}%");
                                        $q->orWhere('note','LIKE',"%{$search}%");
                                        if($type != null){
                                            $q->orWhere('type', $type);
                                        }
                                  })
                                ->orderBy('created_at', 'DESC')
                                ->count();
        }

        $data = array();
        if(!empty($posts))
        {
            $no = 0;
            foreach ($posts as $post)
            {
                $no++;
                $nestedData['no']            = $start+$no;
                $nestedData['created_at']    = Carbon::parse($post->created_at)->format('d M Y H:i:s');
                if($post->type == 'debit'){
                    $nestedData['type'] = '<td><label class="label label-danger">debet</label></td>';
                }else{
                    $nestedData['type'] = '<td><label class="label label-success">kredit</label></td>';
                };
              
                $nestedData['nominal']   = '<td> Rp. '.number_format($post->nominal, 0, '.', '.').'</td>';
                $nestedData['saldo']     = '<td> Rp.'.number_format($post->saldo, 2, '.', '.').'</td>';
                $nestedData['trxid']         = $post->trxid != null?$post->trxid:'-';
                $nestedData['note']          = $post->note;

                $data[] = $nestedData;

            }
        }
          
        $json_data = array(
                    "draw"            => intval($request->input('draw')),  
                    "recordsTotal"    => intval($totalData),  
                    "recordsFiltered" => intval($totalFiltered), 
                    "data"            => $data   
                    );
            
        return json_encode($json_data);
    }
    
    public function redeemVoucher()
    {
        $URL_uri = request()->segment(1).'/'.request()->segment(2);
        $datasubmenu2 = MenuSubmenu::getSubMenuOneMemberURL($URL_uri)->first();

        if($datasubmenu2->status_sub != 0 )
        {
            return view('member.deposit.redeem-voucher.index');
        }
        else
        {
            abort(404);
        }
    }

}