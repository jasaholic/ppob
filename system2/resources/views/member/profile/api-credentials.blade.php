@extends('member.profile.index')
@section('profile')
<div class="box box-solid">
    <div class="box-header with-border">
        <i class="fa fa-unlock"></i>
        <h3 class="box-title">Kredensial API</h3>
    </div>
    <div class="box-body">
        <form role="form" class="form-horizontal" action="{{ route('profile.api_credentials.update') }}" method="post">
            {{ csrf_field() }}
            <div class="box-body">
                
                <div class="form-group">
                    <label class="col-sm-3 control-label">API Key : </label>
                    <div class="col-sm-9">
                        <div class="input-group">
                            <input type="text" class="form-control" value="***************************************" id="api_key" disabled readonly>
                            <span class="input-group-btn">
                                <button class="btn custom__btn-green" data-toggle="modal" data-target="#modal-api-key" type="button">Lihat</button>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="form-group{{ $errors->has('whitelist_ip') ? ' has-error' : '' }}">
                    <label class="col-sm-3 control-label">Whitelist IP : </label>
                    <div class="col-sm-9">
                        <input type="text" name="whitelist_ip" class="form-control" autocomplete="off" value="{{ old('whitelist_ip') ?? $user->whitelist_ip }}">
                        {!! $errors->first('whitelist_ip', '<p class="help-block"><small>:message</small></p>') !!}
                        <p class="help"><small>Daftar alamat IP yang diizinkan mengakses API dipisahkan dengan koma. Contoh: <b>164.56.45.10,34.56.78.90</b></small></p>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="col-sm-3 control-label">Perbarui API Key ? </label>
                    <div class="col-sm-9">
                        <select class="form-control" name="update_key">
                            <option value="0">TIDAK</option>
                            <option value="1">YA</option>
                        </select>
                        <p class="help"><small>Pilih 'YA' untuk menggenerate API Key baru</small></p>
                    </div>
                </div>
                
                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                    <label class="col-sm-3 control-label">Kata Sandi : </label>
                    <div class="col-sm-9">
                        <input type="password" name="password" class="form-control" placeholder="" autocomplete="off">
                        {!! $errors->first('password', '<p class="help-block"><small>:message</small></p>') !!}
                        <p class="help"><small>Kata sandi akun anda saat ini</small></p>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-9">
                        <button type="submit" class="submit btn custom__btn-green btn-block">&nbsp;&nbsp;Update&nbsp;&nbsp;</button>
                    </div>
                </div>
                
            </div>
        </form>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="modal-api-key" aria-labelledby="modal-api-keyLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="modal-api-keyLabel">Masukkan Kata Sandi</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
            <input type="password" name="password" class="form-control" placeholder="" autocomplete="off">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn custom__btn-green" onclick="getApiKey()">Lanjutkan</button>
        <button type="button" class="btn btn-danger" data-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>


<!--<div class="box box-solid">-->
<!--    <div class="box-header with-border">-->
<!--        <i class="fa fa-book"></i>-->
<!--        <h3 class="box-title">Dokumentasi API</h3>-->
<!--    </div>-->
<!--    <div class="box-body">-->
<!--            <div class="box-body">-->
                
<!--                <ul class="nav nav-tabs">-->
<!--                        <li class="active"><a href="#pembelian" data-toggle="tab">PEMBELIAN</a></li>-->
<!--                        <li><a href="#pembayaran" data-toggle="tab">PEMBAYARAN</a></li>-->
<!--                        <li><a href="#trx" data-toggle="tab">TRANSAKSI</a></li>-->
<!--                    </ul>-->
                    
<!--                     <div class="tab-content">-->
<!--                        <div class="tab-pane active" id="pembelian">-->
<!--                            content-->
<!--                            <br/>-->
<!--                            <p><i><u>Kategori, Operator, Produk Pembelian</u></i></p>-->
<!--                            <br/>-->
<!--                            <div class="panel panel-default">-->
<!--                        <div class="panel-heading" role="tab">-->
<!--                            <h4 class="panel-title">-->
<!--                                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#pembeliankategori" aria-expanded="false" aria-controls="pembeliankategori" class="collapsed">-->
<!--                                    <b>API KATEGORI PRODUK PEMBELIAN</b>-->
<!--                                </a>-->
<!--                            </h4>-->
<!--                        </div>-->
<!--                        <div id="pembeliankategori" class="panel-collapse collapse" role="tabpanel" aria-expanded="false">-->
<!--                            <div class="panel-body">-->
<!--                                <h4>Contoh Script PHP : </h4>-->
<!--                                <code style="font-size:12px;">-->
                                    
                                   
<!--                                    &lt;?php<br>-->
<!--                                    $url  = 'https://hijaupay.com/api/v1/product/prabayar/category/';<br>-->
<!--                                    <br>-->
<!--                                    $header = array(<br>-->
<!--                                        'Accept: application/json',<br>-->
<!--                                        'Authorization: Bearer [apikey]', <span class="text-primary">// Ganti [apikey] dengan API KEY Anda</span><br>-->
<!--                                    );<br>-->
<!--                                    <br>-->
<!--                                    $ch = curl_init();<br>-->
<!--                                    curl_setopt($ch, CURLOPT_URL, $url);<br>-->
<!--                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);<br>-->
<!--                                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);<br>-->
<!--                                    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);<br>-->
<!--                                    curl_setopt($ch, CURLOPT_POST, 1);<br>-->
<!--                                    $result = curl_exec($ch);<br>-->
<!--                                    <br>-->
<!--                                    echo $result;<br>-->
                  
                                    
<!--                                </code>-->
<!--                                <br>-->
<!--                                <h4>Contoh Response Sukses : </h4>-->
<!--                                <code style="font-size:12px;">-->
                                    
                                   
<!--                                    {<br>-->
<!--                                        "status": "success",<br>-->
<!--                                        "data": [<br>-->
<!--                                            {<br>-->
<!--                                                "product_id": "pulsa",<br>-->
<!--                                                "product_name": "Pulsa All Operator",<br>-->
<!--                                                "status": "1" <span class="text-primary">// 1 = Tersedia, 0 = Tidak Tersedia </span><br>-->
<!--                                            },<br>-->
<!--                                            {<br>-->
<!--                                                "product_id": "plnpra",<br>-->
<!--                                                "product_name": "Token PLN",<br>-->
<!--                                                "status": "1"<br>-->
<!--                                            },<br>-->
<!--                                            {<br>-->
<!--                                                "product_id": "paket",<br>-->
<!--                                                "product_name": "Paket Internet",<br>-->
<!--                                                "status": "1"<br>-->
<!--                                            },<br>-->
<!--                                            {<br>-->
<!--                                                "product_id": "game",<br>-->
<!--                                                "product_name": "Voucher Game",<br>-->
<!--                                                "status": "0"<br>-->
<!--                                            },<br>-->
<!--                                            {<br>-->
<!--                                                "product_id": "gojek",<br>-->
<!--                                                "product_name": "Saldo GOJEK",<br>-->
<!--                                                "status": "1"<br>-->
<!--                                            },<br>-->
<!--                                            {<br>-->
<!--                                                "product_id": "grab",<br>-->
<!--                                                "product_name": "Saldo GRAB",<br>-->
<!--                                                "status": "0"<br>-->
<!--                                            }<br>-->
<!--                                        ]<br>-->
<!--                                    }<br>-->
                  
                                    
<!--                                </code>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                            <div class="panel panel-default">-->
<!--                        <div class="panel-heading" role="tab">-->
<!--                            <h4 class="panel-title">-->
<!--                                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#pembelianoperator" aria-expanded="false" aria-controls="pembelianoperator" class="collapsed">-->
<!--                                    <b>API OPERATOR PRODUK PEMBELIAN</b>-->
<!--                                </a>-->
<!--                            </h4>-->
<!--                        </div>-->
<!--                        <div id="pembelianoperator" class="panel-collapse collapse" role="tabpanel" aria-expanded="false">-->
<!--                            <div class="panel-body">-->
<!--                                <h4>Contoh Script PHP : </h4>-->
<!--                                <code style="font-size:12px;">-->
                                    
                                   
<!--                                    &lt;?php<br>-->
<!--                                    $url  = 'https://hijaupay.com/api/v1/product/prabayar/operator ;<br>-->
<!--                                    <br>-->
<!--                                    $header = array(<br>-->
<!--                                        'Accept: application/json',<br>-->
<!--                                        'Authorization: Bearer [apikey]', <span class="text-primary">// Ganti [apikey] dengan API KEY Anda</span><br>-->
<!--                                    );<br>-->
<!--                                    <br>-->
<!--                                    $ch = curl_init();<br>-->
<!--                                    curl_setopt($ch, CURLOPT_URL, $url);<br>-->
<!--                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);<br>-->
<!--                                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);<br>-->
<!--                                    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);<br>-->
<!--                                    curl_setopt($ch, CURLOPT_POST, 1);<br>-->
<!--                                    $result = curl_exec($ch);<br>-->
<!--                                    <br>-->
<!--                                    echo $result;<br>-->
                  
                                    
<!--                                </code>-->
<!--                                <br>-->
<!--                                <h4>Contoh Response Sukses : </h4>-->
<!--                                <code style="font-size:12px;">-->
                                    
                                   
<!--                                    {<br>-->
<!--                                        "status": true,<br>-->
<!--                                        "data": [<br>-->
<!--                                            {<br>-->
<!--                                                "id": 1,<br>-->
<!--                                                "product_id": "AX",<br>-->
<!--                                                "product_name": "AXIS",<br>-->
<!--                                                "status": 1,<br>-->
<!--                                                "pembeliankategori_id": 19,<br>-->
<!--                                            },<br>-->
<!--                                            {<br>-->
<!--                                                "id": 2,<br>-->
<!--                                                "product_id": "BY",<br>-->
<!--                                                "product_name": "BY.U",<br>-->
<!--                                                "status": 1,<br>-->
<!--                                                "pembeliankategori_id": 19,<br>-->
<!--                                            },<br>-->
<!--                                            {<br>-->
<!--                                                "id": 3,<br>-->
<!--                                                "product_id": "S",<br>-->
<!--                                                "product_name": "TELKOMSEL",<br>-->
<!--                                                "status": 1,<br>-->
<!--                                                "pembeliankategori_id": 19,<br>-->
<!--                                            },<br>-->
<!--                                            {<br>-->
<!--                                                "id": 4,<br>-->
<!--                                                "product_id": "I",<br>-->
<!--                                                "product_name": "INDOSAT",<br>-->
<!--                                                "status": 1,<br>-->
<!--                                                "pembeliankategori_id": 19,<br>-->
<!--                                            },<br>-->
<!--                                        ]<br>-->
<!--                                    }<br>-->
                  
                                    
<!--                                </code>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                            <div class="panel panel-default">-->
<!--                        <div class="panel-heading" role="tab">-->
<!--                            <h4 class="panel-title">-->
<!--                                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#pembelianproduk" aria-expanded="false" aria-controls="pembelianproduk" class="collapsed">-->
<!--                                    <b>API PRODUK PEMBELIAN</b>-->
<!--                                </a>-->
<!--                            </h4>-->
<!--                        </div>-->
<!--                        <div id="pembelianproduk" class="panel-collapse collapse" role="tabpanel" aria-expanded="false">-->
<!--                            <div class="panel-body">-->
<!--                                <h4>Contoh Script PHP : </h4>-->
<!--                                <code style="font-size:12px;">-->
                                    
                                   
<!--                                    &lt;?php<br>-->
<!--                                    $url  = 'https://hijaupay.com/api/v1/product/prabayar';<br>-->
<!--                                    <br>-->
<!--                                    $header = array(<br>-->
<!--                                        'Accept: application/json',<br>-->
<!--                                        'Authorization: Bearer [apikey]', <span class="text-primary">// Ganti [apikey] dengan API KEY Anda</span><br>-->
<!--                                    );<br>-->
<!--                                    <br>-->
<!--                                    $ch = curl_init();<br>-->
<!--                                    curl_setopt($ch, CURLOPT_URL, $url);<br>-->
<!--                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);<br>-->
<!--                                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);<br>-->
<!--                                    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);<br>-->
<!--                                    curl_setopt($ch, CURLOPT_POST, 1);<br>-->
<!--                                    $result = curl_exec($ch);<br>-->
<!--                                    <br>-->
<!--                                    echo $result;<br>-->
                  
                                    
<!--                                </code>-->
<!--                                <br>-->
<!--                                <h4>Contoh Response Sukses : </h4>-->
<!--                                <code style="font-size:12px;">-->
<!--                                    {<br>-->
<!--                                        "status": "success",<br>-->
<!--                                        "data": [<br>-->
<!--                                            {<br>-->
<!--                                                "id": 1,<br>-->
<!--                                                "product_id": "AX5",<br>-->
<!--                                                "pembelianoperator_id": 1,<br>-->
<!--                                                "pembeliankategori_id": 1,<br>-->
<!--                                                "product_name": "AXIS 5000",<br>-->
<!--                                                "price": 5585,<br>-->
<!--                                                "status": 1,<br>-->
<!--                                            },<br>-->
<!--                                            {<br>-->
<!--                                                "id": 2,<br>-->
<!--                                                "product_id": "AX10",<br>-->
<!--                                                "pembelianoperator_id": 1,<br>-->
<!--                                                "pembeliankategori_id": 1,<br>-->
<!--                                                "product_name": "AXIS 10000",<br>-->
<!--                                                "price": 10585,<br>-->
<!--                                                "status": 1,<br>-->
<!--                                            },<br>-->
<!--                                            {<br>-->
<!--                                                "id": 3,<br>-->
<!--                                                "product_id": "AX25",<br>-->
<!--                                                "pembelianoperator_id": 1,<br>-->
<!--                                                "pembeliankategori_id": 1,<br>-->
<!--                                                "product_name": "AXIS 25000",<br>-->
<!--                                                "price": 24695,<br>-->
<!--                                                "status": 1,<br>-->
<!--                                            },<br>-->
<!--                                            {<br>-->
<!--                                                "id": 4,<br>-->
<!--                                                "product_id": "AX30",<br>-->
<!--                                                "pembelianoperator_id": 1,<br>-->
<!--                                                "pembeliankategori_id": 1,<br>-->
<!--                                                "product_name": "AXIS 30000",<br>-->
<!--                                                "price": 29970,<br>-->
<!--                                                "status": 1,<br>-->
<!--                                            },<br>-->
<!--                                            {<br>-->
<!--                                                "id": 5,<br>-->
<!--                                                "product_id": "AX50",<br>-->
<!--                                                "pembelianoperator_id": 1,<br>-->
<!--                                                "pembeliankategori_id": 1,<br>-->
<!--                                                "product_name": "AXIS 50000",<br>-->
<!--                                                "price": 49345,<br>-->
<!--                                                "status": 1,<br>-->
<!--                                            },<br>-->
<!--                                        ]<br>-->
<!--                                    }<br>-->
                  
                                    
<!--                                </code>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div> -->
<!--                    </div>-->
<!--                        <div class="tab-pane" id="pembayaran">-->
<!--                            content-->
<!--                            <br/>-->
<!--                            <p><i><u>Kategori, Operator, Produk</u></i></p>-->
<!--                            <br/>-->
<!--                            <div class="panel panel-default">-->
<!--                        <div class="panel-heading" role="tab">-->
<!--                            <h4 class="panel-title">-->
<!--                                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#pembayarankategori" aria-expanded="false" aria-controls="pembayarankategori" class="collapsed">-->
<!--                                    <b>API KATEGORI PRODUK PEMBAYARAN</b>-->
<!--                                </a>-->
<!--                            </h4>-->
<!--                        </div>-->
<!--                        <div id="pembayarankategori" class="panel-collapse collapse" role="tabpanel" aria-expanded="false">-->
<!--                            <div class="panel-body">-->
<!--                                <h4>Contoh Script PHP : </h4>-->
<!--                                <code style="font-size:12px;">-->
                                    
                                   
<!--                                    &lt;?php<br>-->
<!--                                    $url  = 'https://hijaupay.com/api/v1/product/pascabayar/category/';<br>-->
<!--                                    <br>-->
<!--                                    $header = array(<br>-->
<!--                                        'Accept: application/json',<br>-->
<!--                                        'Authorization: Bearer [apikey]', <span class="text-primary">// Ganti [apikey] dengan API KEY Anda</span><br>-->
<!--                                    );<br>-->
<!--                                    <br>-->
<!--                                    $ch = curl_init();<br>-->
<!--                                    curl_setopt($ch, CURLOPT_URL, $url);<br>-->
<!--                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);<br>-->
<!--                                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);<br>-->
<!--                                    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);<br>-->
<!--                                    curl_setopt($ch, CURLOPT_POST, 1);<br>-->
<!--                                    $result = curl_exec($ch);<br>-->
<!--                                    <br>-->
<!--                                    echo $result;<br>-->
                  
                                    
<!--                                </code>-->
<!--                                <br>-->
<!--                                <h4>Contoh Response Sukses : </h4>-->
<!--                                <code style="font-size:12px;">-->
<!--                                {<br>-->
<!--                                        "status": "success",<br>-->
<!--                                        "data": [<br>-->
<!--                                            {<br>-->
<!--                                                "id": "4",<br>-->
<!--                                                "product_name": "Asuransi (BPJS)",<br>-->
<!--                                                "status": "1" <span class="text-primary">// 1 = Tersedia, 0 = Tidak Tersedia </span><br>-->
<!--                                            },<br>-->
<!--                                            {<br>-->
<!--                                                "id": "5",<br>-->
<!--                                                "product_name": "PPOB Tagihan",<br>-->
<!--                                                "status": "1"<br>-->
<!--                                            },<br>-->
<!--                                        ]<br>-->
<!--                                    }<br>-->
                  
                                    
<!--                                </code>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                            <div class="panel panel-default">-->
<!--                        <div class="panel-heading" role="tab">-->
<!--                            <h4 class="panel-title">-->
<!--                                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#pembayaranoperator" aria-expanded="false" aria-controls="pembayaranoperator" class="collapsed">-->
<!--                                    <b>API OPERATOR PRODUK PEMBAYARAN</b>-->
<!--                                </a>-->
<!--                            </h4>-->
<!--                        </div>-->
<!--                        <div id="pembayaranoperator" class="panel-collapse collapse" role="tabpanel" aria-expanded="false">-->
<!--                            <div class="panel-body">-->
<!--                                <h4>Contoh Script PHP : </h4>-->
<!--                                <code style="font-size:12px;">-->
                                    
                                   
<!--                                    &lt;?php<br>-->
<!--                                    $url  = 'https://hijaupay.com/api/v1/product/pascabayar/operator/';<br>-->
<!--                                    <br>-->
<!--                                    $header = array(<br>-->
<!--                                        'Accept: application/json',<br>-->
<!--                                        'Authorization: Bearer [apikey]', <span class="text-primary">// Ganti [apikey] dengan API KEY Anda</span><br>-->
<!--                                    );<br>-->
<!--                                    <br>-->
<!--                                    $ch = curl_init();<br>-->
<!--                                    curl_setopt($ch, CURLOPT_URL, $url);<br>-->
<!--                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);<br>-->
<!--                                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);<br>-->
<!--                                    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);<br>-->
<!--                                    curl_setopt($ch, CURLOPT_POST, 1);<br>-->
<!--                                    $result = curl_exec($ch);<br>-->
<!--                                    <br>-->
<!--                                    echo $result;<br>-->
                  
                                    
<!--                                </code>-->
<!--                                <br>-->
<!--                                <h4>Contoh Response Sukses : </h4>-->
<!--                                <code style="font-size:12px;">-->
<!--                                    {<br>-->
<!--                                        "status": true,<br>-->
<!--                                        "data": [<br>-->
<!--                                            {<br>-->
<!--                                                "id": 1,<br>-->
<!--                                                "product_name": "PPOB Multifinance",<br>-->
<!--                                                "status": 1,<br>-->
<!--                                                "pembayarankategori_id": 4,<br>-->
<!--                                            },<br>-->
<!--                                            {<br>-->
<!--                                                "id": 2,<br>-->
<!--                                                "product_name": "PPOB Tagihan",<br>-->
<!--                                                "status": 1,<br>-->
<!--                                                "pembayarankategori_id": 5,<br>-->
<!--                                            },<br>-->
<!--                                        ]<br>-->
<!--                                    }<br>-->
                  
                                    
<!--                                </code>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                            <div class="panel panel-default">-->
<!--                                <div class="panel-heading" role="tab">-->
<!--                                    <h4 class="panel-title">-->
<!--                                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#pembayaranproduk" aria-expanded="false" aria-controls="pembayaranproduk" class="collapsed">-->
<!--                                            <b>API PRODUK PEMBAYARAN</b>-->
<!--                                        </a>-->
<!--                                    </h4>-->
<!--                                </div>-->
<!--                                <div id="pembayaranproduk" class="panel-collapse collapse" role="tabpanel" aria-expanded="false">-->
<!--                                    <div class="panel-body">-->
<!--                                        <h4>Contoh Script PHP : </h4>-->
<!--                                        <code style="font-size:12px;">-->
                                            
                                           
<!--                                            &lt;?php<br>-->
<!--                                            $url  = 'https://hijaupay.com/api/v1/product/pascabayar';<br>-->
<!--                                            <br>-->
<!--                                            $header = array(<br>-->
<!--                                                'Accept: application/json',<br>-->
<!--                                                'Authorization: Bearer [apikey]', <span class="text-primary">// Ganti [apikey] dengan API KEY Anda</span><br>-->
<!--                                            );<br>-->
<!--                                            <br>-->
<!--                                            $ch = curl_init();<br>-->
<!--                                            curl_setopt($ch, CURLOPT_URL, $url);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_POST, 1);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);<br>-->
<!--                                            $result = curl_exec($ch);<br>-->
<!--                                            <br>-->
<!--                                            echo $result;<br>-->
                          
                                            
<!--                                        </code>-->
<!--                                        <br>-->
<!--                                        <h4>Contoh Response Sukses : </h4>-->
<!--                                        <code style="font-size:12px;">-->
<!--                                            {<br>-->
<!--                                                "status": "success",<br>-->
<!--                                                "data": [<br>-->
<!--                                                    {<br>-->
<!--                                                        "id": 1,<br>-->
<!--                                                        "pembayaranoperator_id": "1",<br>-->
<!--                                                        "pembayarankategori_id": 4,<br>-->
<!--                                                        "product_name": "Adira Finance",<br>-->
<!--                                                        "code": "ADIRA",<br>-->
<!--                                                        "price_markup": 3500,<br>-->
<!--                                                        "status": 1,<br>-->
<!--                                                    },<br>-->
<!--                                                    {<br>-->
<!--                                                        "id": 1,<br>-->
<!--                                                        "pembayaranoperator_id": "1",<br>-->
<!--                                                        "pembayarankategori_id": 4,<br>-->
<!--                                                        "product_name": "Bussan Auto Finance",<br>-->
<!--                                                        "code": "BAF",<br>-->
<!--                                                        "price_markup": 3000,<br>-->
<!--                                                        "status": 1,<br>-->
<!--                                                    },<br>-->
<!--                                                    {<br>-->
<!--                                                        "id": 1,<br>-->
<!--                                                        "pembayaranoperator_id": "1",<br>-->
<!--                                                        "pembayarankategori_id": 4,<br>-->
<!--                                                        "product_name": "Wahana Ottomitra Multiartha",<br>-->
<!--                                                        "code": "WOM",<br>-->
<!--                                                        "price_markup": 3000,<br>-->
<!--                                                        "status": 1,<br>-->
<!--                                                    },<br>-->
<!--                                                ]<br>-->
<!--                                            }<br>                  -->
<!--                                        </code>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                    </div> -->
<!--                        </div>-->
<!--                        <div class="tab-pane" id="trx">-->
<!--                            content-->
<!--                            <br/>-->
<!--                            <p><i><u>Transaksi Pembelian Dan Pembayaran</u></i></p>-->
<!--                            <br/>-->
<!--                            <div class="panel panel-default">-->
<!--                        <div class="panel-heading" role="tab">-->
<!--                            <h4 class="panel-title">-->
<!--                                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#ceksaldo" aria-expanded="false" aria-controls="ceksaldo" class="collapsed">-->
<!--                                    <b>API CEK SALDO</b>-->
<!--                                </a>-->
<!--                            </h4>-->
<!--                        </div>-->
<!--                        <div id="ceksaldo" class="panel-collapse collapse" role="tabpanel" aria-expanded="false" style="height: 0px;">-->
<!--                            <div class="panel-body">-->
<!--                                <h4>Contoh Script PHP : </h4>-->
<!--                                <code style="font-size:12px;">-->
                        
                                   
<!--                                    &lt;?php<br>-->
<!--                                    $url  = 'https://hijaupay.com/api/v1/balance';<br>-->
<!--                                    <br>-->
<!--                                    $header = array(<br>-->
<!--                                        'Accept: application/json',<br>-->
<!--                                        'Authorization: Bearer [apikey]', <span class="text-primary">// Ganti [apikey] dengan API KEY Anda</span><br>-->
<!--                                    );<br>-->
<!--                                    <br>-->
<!--                                    $ch = curl_init();<br>-->
<!--                                    curl_setopt($ch, CURLOPT_URL, $url);<br>-->
<!--                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);<br>-->
<!--                                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);<br>-->
<!--                                    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);<br>-->
<!--                                    curl_setopt($ch, CURLOPT_POST, 1);<br>-->
<!--                                    $result = curl_exec($ch);<br>-->
<!--                                    <br>-->
<!--                                    echo $result;<br>-->
                  
                                    
<!--                                </code>-->
<!--                                <br>-->
<!--                                <h4>Contoh Response Sukses : </h4>-->
<!--                                <code style="font-size:12px;">-->
<!--                                    {<br>-->
<!--                                        "status": true,<br>-->
<!--                                        "balance": "150000",<br>-->
<!--                                    }<br>-->
                  
                                    
<!--                                </code>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                            <div class="panel panel-default">-->
<!--                                <div class="panel-heading" role="tab">-->
<!--                                    <h4 class="panel-title">-->
<!--                                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#pembeliantransaksi" aria-expanded="false" aria-controls="pembeliantransaksi" class="collapsed">-->
<!--                                            <b>API TRANSAKSI PEMBELIAN</b>-->
<!--                                        </a>-->
<!--                                    </h4>-->
<!--                                </div>-->
<!--                                <div id="pembeliantransaksi" class="panel-collapse collapse" role="tabpanel" aria-expanded="false">-->
<!--                                    <div class="panel-body">-->
<!--                                        <h4>Contoh Script PHP : </h4>-->
<!--                                        <code style="font-size:12px;">-->
                                            
                                           
<!--                                            &lt;?php<br>-->
<!--                                            $url  = 'https://hijaupay.com/api/v1/transaction/prabayar/create';<br>-->
<!--                                            <br>-->
<!--                                            $header = array(<br>-->
<!--                                                'Accept: application/json',<br>-->
<!--                                                'Authorization: Bearer [apikey]', <span class="text-primary">// Ganti [apikey] dengan API KEY Anda</span><br>-->
<!--                                            );<br>-->
<!--                                            <br>-->
<!--                                            $data = array(  <br> -->
<!--                                              'inquiry' =&gt; 'I', <span class="text-primary">// konstan I OR PLN</span><br>-->
<!--                                              'code'    =&gt; 'IDH1', <span class="text-primary">// kode produk</span><br>-->
<!--                                              'phone'   =&gt; '085800000000', <span class="text-primary">// nohp pembeli</span><br>-->
<!--                                              'pin'     =&gt; '3964', <span class="text-primary">// pin member</span><br>-->
<!--                                              );<br>-->
<!--                                            <br>-->
<!--                                            $ch = curl_init();<br>-->
<!--                                            curl_setopt($ch, CURLOPT_URL, $url);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_POST, 1);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);<br>-->
<!--                                            $result = curl_exec($ch);<br>-->
<!--                                            <br>-->
<!--                                            echo $result;<br>-->
                          
                                            
<!--                                        </code>-->
<!--                                        <br>-->
<!--                                        <h4>Contoh Response Sukses : </h4>-->
<!--                                        <code style="font-size:12px;">-->
                                            
                                           
<!--                                            {<br>-->
<!--                                                "status": true,<br>-->
<!--                                                "transaksi_id": 12312,<br>-->
<!--                                                "message":"Pembelian anda telah diantrikan."<br>-->
<!--                                                "message": "Transaksi Pembelian Token PLN 20.000 14534234234 Berhasil Diproses."<br>-->
<!--                                            }<br>-->
                                            
<!--                                        </code>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                    </div>-->
<!--                            <div class="panel panel-default">-->
<!--                                <div class="panel-heading" role="tab">-->
<!--                                    <h4 class="panel-title">-->
<!--                                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#riwayatpembeliantransaksi" aria-expanded="false" aria-controls="pembeliantransaksi" class="collapsed">-->
<!--                                            <b>API RIWAYAT TRANSAKSI PEMBELIAN</b>-->
<!--                                        </a>-->
<!--                                    </h4>-->
<!--                                </div>-->
<!--                                <div id="riwayatpembeliantransaksi" class="panel-collapse collapse" role="tabpanel" aria-expanded="false">-->
<!--                                   <div class="panel-body">-->
<!--                                <h4>Contoh Script PHP : </h4>-->
<!--                                <code style="font-size:12px;">-->
                        
                                   
<!--                                    &lt;?php<br>-->
<!--                                    $url  = 'https://hijaupay.com/api/v1/transaction/prabayar/history';<br>-->
<!--                                    <br>-->
<!--                                    $header = array(<br>-->
<!--                                        'Accept: application/json',<br>-->
<!--                                        'Authorization: Bearer [apikey]', <span class="text-primary">// Ganti [apikey] dengan API KEY Anda</span><br>-->
<!--                                    );<br>-->
<!--                                    <br>-->
<!--                                    $ch = curl_init();<br>-->
<!--                                    curl_setopt($ch, CURLOPT_URL, $url);<br>-->
<!--                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);<br>-->
<!--                                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);<br>-->
<!--                                    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);<br>-->
<!--                                    curl_setopt($ch, CURLOPT_POST, 1);<br>-->
<!--                                    $result = curl_exec($ch);<br>-->
<!--                                    <br>-->
<!--                                    echo $result;<br>-->
                  
                                    
<!--                                </code>-->
<!--                                <br>-->
<!--                                <h4>Contoh Response Sukses : </h4>-->
<!--                                <code style="font-size:12px;">-->
<!--                                    {<br>-->
<!--                                        "trxid": 20,<br>-->
<!--                                        "api_trxid": "INVXXX",<br>-->
<!--                                        "tagihan_id": "",<br>-->
<!--                                        "code": "T10",<br>-->
<!--                                        "produk": "THREE 10000",<br>-->
<!--                                        "harga": 9990,<br>-->
<!--                                        "target": "0895385987792",<br>-->
<!--                                        "mtrpln": "-",<br>-->
<!--                                        "note": "Transaksi success",<br>-->
<!--                                        "token": "1029230852134******",<br>-->
<!--                                        "status": 1,<br>-->
<!--                                        "saldo_before_trx": 51186227,<br>-->
<!--                                        "saldo_after_trx": 51176187,<br>-->
<!--                                        "created_at": "2017-10-29 23:08:47",<br>-->
<!--                                        "updated_at": "2018-01-08 14:40:28"<br>-->
<!--                                    }<br>-->
                  
                                    
<!--                                </code>-->
<!--                            </div>-->
<!--                                </div>-->
<!--                    </div>-->
<!--                            <div class="panel panel-default">-->
<!--                                <div class="panel-heading" role="tab">-->
<!--                                    <h4 class="panel-title">-->
<!--                                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#detailTransaksipascaByID" aria-expanded="false" aria-controls="detailTransaksiByID" class="collapsed">-->
<!--                                            <b>API DETAIL TRANSAKSI BY TRX ID</b>-->
<!--                                        </a>-->
<!--                                    </h4>-->
<!--                                </div>-->
<!--                                <div id="detailTransaksipascaByID" class="panel-collapse collapse" role="tabpanel" aria-expanded="false">-->
<!--                                    <div class="panel-body">-->
<!--                                        <h4>Contoh Script PHP : </h4>-->
<!--                                        <code style="font-size:12px;">-->
                                            
                                           
<!--                                            &lt;?php<br>-->
<!--                                            $url  = 'https://hijaupay.com/api/v1/transaction/pascabayar/detail';<br>-->
<!--                                            <br>-->
<!--                                            $header = array(<br>-->
<!--                                                'Accept: application/json',<br>-->
<!--                                                'Authorization: Bearer [apikey]', <span class="text-primary">// Ganti [apikey] dengan API KEY Anda</span><br>-->
<!--                                            );<br>-->
<!--                                            <br>-->
<!--                                            $data = array(  <br>-->
<!--                                              'trxid' =&gt; '2554', <span class="text-primary">// Masukkan Transaksi ID</span><br>-->
<!--                                              );<br>-->
<!--                                            <br>-->
<!--                                            $ch = curl_init();<br>-->
<!--                                            curl_setopt($ch, CURLOPT_URL, $url);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_POST, 1);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);<br>-->
<!--                                            $result = curl_exec($ch);<br>-->
<!--                                            <br>-->
<!--                                            echo $result;<br>-->
                          
                                            
<!--                                        </code>-->
<!--                                        <br>-->
<!--                                        <h4>Contoh Response Sukses : </h4>-->
<!--                                        <code style="font-size:12px;">-->
                                            
                                           
<!--                                            {<br>-->
<!--                                                "status": "success",<br>-->
<!--                                                "data": {<br>-->
<!--                                                    "id": 2554,<br>-->
<!--                                                    "code": "IDPH2",<br>-->
<!--                                                    "produk": "Unlimited + 2GB - Promo (Ratu)",<br>-->
<!--                                                    "total": "29450",<br>-->
<!--                                                    "target": "0857966*****",<br>-->
<!--                                                    "mtrpln": "-",<br>-->
<!--                                                    "note": "Trx Unlimited + 2GB - Promo (Ratu) 0857966***** Sukses. Transaksi Berhasil SN : 011728000047918*****",<br>-->
<!--                                                    "token": "011728000047918*****",<br>-->
<!--                                                    "status": "1", <span class="text-primary">// 0 = Proses, 1 = Sukses, 2 = Gagal, 3 = Refund</span><br>-->
<!--                                                    "saldo_before_trx": "461381",<br>-->
<!--                                                    "saldo_after_trx": "431781",<br>-->
<!--                                                    "created_at": "2018-03-18 21:18:09",<br>-->
<!--                                                    "updated_at": "2018-03-18 21:18:32"<br>-->
<!--                                                }<br>-->
<!--                                            }<br>-->
                                            
<!--                                        </code>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                    </div>-->
<!--                            <div class="panel panel-default">-->
<!--                                <div class="panel-heading" role="tab">-->
<!--                                    <h4 class="panel-title">-->
<!--                                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#cekTagihan" aria-expanded="false" aria-controls="cekTagihan" class="collapsed">-->
<!--                                            <b>API CEK TAGIHAN PEMBAYARAN</b>-->
<!--                                        </a>-->
<!--                                    </h4>-->
<!--                                </div>-->
<!--                                <div id="cekTagihan" class="panel-collapse collapse" role="tabpanel" aria-expanded="false">-->
<!--                                    <div class="panel-body">-->
<!--                                        <h4>Contoh Script PHP : </h4>-->
<!--                                        <code style="font-size:12px;">-->
                                            
                                           
<!--                                            &lt;?php<br>-->
<!--                                            $url  = 'https://hijaupay.com/api/v1/transaction/pascabayar/check';<br>-->
<!--                                            <br>-->
<!--                                            $header = array(<br>-->
<!--                                                'Accept: application/json',<br>-->
<!--                                                'Authorization: Bearer [apikey]', <span class="text-primary">// Ganti [apikey] dengan API KEY Anda</span><br>-->
<!--                                            );<br>-->
<!--                                            <br>-->
<!--                                            $data = array(  <br>-->
<!--                                              'product' =&gt; 'PLN', <span class="text-primary">// Masukkan ID Produk (exp : PLN)</span><br>-->
<!--                                              'phone' =&gt; '085800000864', <span class="text-primary">// Masukkan No.hp Anda</span><br>-->
<!--                                              'no_pelanggan' =&gt; '545646555565', <span class="text-primary">// Masukkan ID Pelanggan (exp: no.meteran/ id pembayaran)</span><br>-->
<!--                                              'pin' =&gt; '2554', <span class="text-primary">// Masukkan PIN user (anda)</span><br>-->
<!--                                              );<br>-->
<!--                                            <br>-->
<!--                                            $ch = curl_init();<br>-->
<!--                                            curl_setopt($ch, CURLOPT_URL, $url);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_POST, 1);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);<br>-->
<!--                                            $result = curl_exec($ch);<br>-->
<!--                                            <br>-->
<!--                                            echo $result;<br>-->
                          
                                            
<!--                                        </code>-->
<!--                                        <br>-->
<!--                                        <h4>Contoh Response Sukses : </h4>-->
<!--                                        <code style="font-size:12px;">-->
                                            
                                           
<!--                                            {<br>-->
<!--                                                "status": "success",<br>-->
<!--                                                "data": {<br>-->
<!--                                                    "id": 145,<br>-->
<!--                                                    "tagihan_id": 235142,<br>-->
<!--                                                    "product_name": "PLN",<br>-->
<!--                                                    "phone": "0858000*****",<br>-->
<!--                                                    "no_pelanggan": "5150904*****",<br>-->
<!--                                                    "nama": "SUTAR.",<br>-->
<!--                                                    "periode": "APR18",<br>-->
<!--                                                    "status": 0,<br>-->
<!--                                                    "expired": 1,<br>-->
<!--                                                    "jumlah_tagihan": 175490,<br>-->
<!--                                                    "admin": 3000,<br>-->
<!--                                                    "jumlah_bayar": 178490,<br>-->
<!--                                                    "user_id": 8,<br>-->
<!--                                                    "via": "API",<br>-->
<!--                                                    "created_at": "2018-03-18 21:18:09",<br>-->
<!--                                                    "updated_at": "2018-03-18 21:18:32"<br>-->
<!--                                                }<br>-->
<!--                                            }<br>-->
                                            
<!--                                        </code>-->
                                        
<!--                                    </div>-->
<!--                                </div>-->
<!--                    </div>-->
<!--                            <div class="panel panel-default">-->
<!--                                <div class="panel-heading" role="tab">-->
<!--                                    <h4 class="panel-title">-->
<!--                                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#bayarTagihan" aria-expanded="false" aria-controls="bayarTagihan" class="collapsed">-->
<!--                                            <b>API BAYAR TAGIHAN PEMBAYARAN</b>-->
<!--                                        </a>-->
<!--                                    </h4>-->
<!--                                </div>-->
<!--                                <div id="bayarTagihan" class="panel-collapse collapse" role="tabpanel" aria-expanded="false">-->
<!--                                    <div class="panel-body">-->
<!--                                        <h4>Contoh Script PHP : </h4>-->
<!--                                        <code style="font-size:12px;">-->
                                            
                                           
<!--                                            &lt;?php<br>-->
<!--                                            $url  = 'https://hijaupay.com/api/v1/transaction/pascabayar/pay';<br>-->
<!--                                            <br>-->
<!--                                            $header = array(<br>-->
<!--                                                'Accept: application/json',<br>-->
<!--                                                'Authorization: Bearer [apikey]', <span class="text-primary">// Ganti [apikey] dengan API KEY Anda</span><br>-->
<!--                                            );<br>-->
<!--                                            <br>-->
<!--                                            $data = array(  <br>-->
<!--                                              'product' =&gt; 'PLN', <span class="text-primary">// Masukkan ID Produk (exp : PLN)</span><br>-->
<!--                                              'phone' =&gt; '085800000864', <span class="text-primary">// Masukkan No.hp Anda</span><br>-->
<!--                                              'no_pelanggan' =&gt; '545646555565', <span class="text-primary">// Masukkan ID Pelanggan (exp: no.meteran/ id pembayaran)</span><br>-->
<!--                                              'pin' =&gt; '2554', <span class="text-primary">// Masukkan PIN user (anda)</span><br>-->
<!--                                              );<br>-->
<!--                                            <br>-->
<!--                                            $ch = curl_init();<br>-->
<!--                                            curl_setopt($ch, CURLOPT_URL, $url);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_POST, 1);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);<br>-->
<!--                                            $result = curl_exec($ch);<br>-->
<!--                                            <br>-->
<!--                                            echo $result;<br>-->
                          
                                            
<!--                                        </code>-->
<!--                                        <br>-->
<!--                                        <h4>Contoh Response Sukses : </h4>-->
<!--                                        <code style="font-size:12px;">-->
                                            
                                           
<!--                                            {<br>-->
<!--                                                "success": true,<br>-->
<!--                                                "message":"Pembayaran Berhasil Diproses."<br>-->
<!--                                            }<br>-->
                                            
<!--                                        </code>-->
                                        
<!--                                    </div>-->
<!--                                </div>-->
<!--                    </div>-->
<!--                            <div class="panel panel-default">-->
<!--                                <div class="panel-heading" role="tab">-->
<!--                                    <h4 class="panel-title">-->
<!--                                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#histori" aria-expanded="false" aria-controls="histori" class="collapsed">-->
<!--                                            <b>API RIWAYAT TRANSAKSI PEMBAYARAN</b>-->
<!--                                        </a>-->
<!--                                    </h4>-->
<!--                                </div>-->
<!--                                <div id="histori" class="panel-collapse collapse" role="tabpanel" aria-expanded="false">-->
<!--                                    <div class="panel-body">-->
<!--                                        <h4>Contoh Script PHP : </h4>-->
<!--                                        <code style="font-size:12px;">-->
                                            
                                           
<!--                                            &lt;?php<br>-->
<!--                                            $url  = 'https://hijaupay.com/api/v1/transaction/pascabayar/history';<br>-->
<!--                                            <br>-->
<!--                                            $header = array(<br>-->
<!--                                                'Accept: application/json',<br>-->
<!--                                                'Authorization: Bearer [apikey]', <span class="text-primary">// Ganti [apikey] dengan API KEY Anda</span><br>-->
<!--                                            );<br>-->
<!--                                            <br>-->
<!--                                            $ch = curl_init();<br>-->
<!--                                            curl_setopt($ch, CURLOPT_URL, $url);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_POST, 1);<br>-->
<!--                                            $result = curl_exec($ch);<br>-->
<!--                                            <br>-->
<!--                                            echo $result;<br>-->
                          
                                            
<!--                                        </code>-->
<!--                                        <br>-->
<!--                                        <h4>Contoh Response Sukses : </h4>-->
<!--                                        <code style="font-size:12px;">-->
<!--                                            {<br>-->
<!--                                                "status": "success",<br>-->
<!--                                                "data": [<br>-->
<!--                                                    {<br>-->
<!--                                                        "id": 20,<br>-->
<!--                                                        "order_id": 20,<br>-->
<!--                                                        "tagihan_id": "",<br>-->
<!--                                                        "code": "T10",<br>-->
<!--                                                        "produk": "THREE 10000",<br>-->
<!--                                                        "harga_default": 9820,<br>-->
<!--                                                        "harga_markup": 170,<br>-->
<!--                                                        "total": 9990,<br>-->
<!--                                                        "target": "0895385987792",<br>-->
<!--                                                        "mtrpln": "-",<br>-->
<!--                                                        "note": "Transaksi success",<br>-->
<!--                                                        "pengirim": "116.206.41.5",<br>-->
<!--                                                        "token": "1029230852134******",<br>-->
<!--                                                        "status": 1,<br>-->
<!--                                                        "user_id": 8,<br>-->
<!--                                                        "via": "DIRECT",<br>-->
<!--                                                        "saldo_before_trx": 51186227,<br>-->
<!--                                                        "saldo_after_trx": 51176187,<br>-->
<!--                                                        "created_at": "2017-10-29 23:08:47",<br>-->
<!--                                                        "updated_at": "2018-01-08 14:40:28"<br>-->
<!--                                                    },<br>-->
<!--                                                    {<br>-->
<!--                                                        "id": 21,<br>-->
<!--                                                        "order_id": 21,<br>-->
<!--                                                        "tagihan_id": "",<br>-->
<!--                                                        "code": "S10",<br>-->
<!--                                                        "produk": "TELKOMSEL 10000",<br>-->
<!--                                                        "harga_default": 10335,<br>-->
<!--                                                        "harga_markup": 170,<br>-->
<!--                                                        "total": 10505,<br>-->
<!--                                                        "target": "082299741020",<br>-->
<!--                                                        "mtrpln": "-",<br>-->
<!--                                                        "note": "Trx S10 082299***** Sukses. Transaksi Berhasil SN : 7103014243********",<br>-->
<!--                                                        "pengirim": "120.188.65.50",<br>-->
<!--                                                        "token": "7103014243001******",<br>-->
<!--                                                        "status": 1,<br>-->
<!--                                                        "user_id": 8,<br>-->
<!--                                                        "via": "DIRECT",<br>-->
<!--                                                        "saldo_before_trx": 51176187,<br>-->
<!--                                                        "saldo_after_trx": 51165632,<br>-->
<!--                                                        "created_at": "2017-10-30 14:24:26",<br>-->
<!--                                                        "updated_at": "2017-10-30 14:25:04"<br>-->
<!--                                                    }<br>-->
<!--                                                ]<br>-->
<!--                                            }<br>-->
                                            
<!--                                        </code>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                    </div>-->
<!--                            <div class="panel panel-default">-->
<!--                                <div class="panel-heading" role="tab">-->
<!--                                    <h4 class="panel-title">-->
<!--                                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#detailTransaksiByID" aria-expanded="false" aria-controls="detailTransaksiByID" class="collapsed">-->
<!--                                            <b>API DETAIL TRANSAKSI BY TRX ID</b>-->
<!--                                        </a>-->
<!--                                    </h4>-->
<!--                                </div>-->
<!--                                <div id="detailTransaksiByID" class="panel-collapse collapse" role="tabpanel" aria-expanded="false">-->
<!--                                    <div class="panel-body">-->
<!--                                        <h4>Contoh Script PHP : </h4>-->
<!--                                        <code style="font-size:12px;">-->
                                            
                                           
<!--                                            &lt;?php<br>-->
<!--                                            $url  = 'https://hijaupay.com/api/v1/transaction/pascabayar/detail';<br>-->
<!--                                            <br>-->
<!--                                            $header = array(<br>-->
<!--                                                'Accept: application/json',<br>-->
<!--                                                'Authorization: Bearer [apikey]', <span class="text-primary">// Ganti [apikey] dengan API KEY Anda</span><br>-->
<!--                                            );<br>-->
<!--                                            <br>-->
<!--                                            $data = array(  <br>-->
<!--                                              'trxid' =&gt; '2554', <span class="text-primary">// Masukkan Transaksi ID</span><br>-->
<!--                                              );<br>-->
<!--                                            <br>-->
<!--                                            $ch = curl_init();<br>-->
<!--                                            curl_setopt($ch, CURLOPT_URL, $url);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_POST, 1);<br>-->
<!--                                            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);<br>-->
<!--                                            $result = curl_exec($ch);<br>-->
<!--                                            <br>-->
<!--                                            echo $result;<br>-->
                          
                                            
<!--                                        </code>-->
<!--                                        <br>-->
<!--                                        <h4>Contoh Response Sukses : </h4>-->
<!--                                        <code style="font-size:12px;">-->
                                            
                                           
<!--                                            {<br>-->
<!--                                                "status": "success",<br>-->
<!--                                                "data": {<br>-->
<!--                                                    "id": 2554,<br>-->
<!--                                                    "code": "IDPH2",<br>-->
<!--                                                    "produk": "Unlimited + 2GB - Promo (Ratu)",<br>-->
<!--                                                    "total": "29450",<br>-->
<!--                                                    "target": "0857966*****",<br>-->
<!--                                                    "mtrpln": "-",<br>-->
<!--                                                    "note": "Trx Unlimited + 2GB - Promo (Ratu) 0857966***** Sukses. Transaksi Berhasil SN : 011728000047918*****",<br>-->
<!--                                                    "token": "011728000047918*****",<br>-->
<!--                                                    "status": "1", <span class="text-primary">// 0 = Proses, 1 = Sukses, 2 = Gagal, 3 = Refund</span><br>-->
<!--                                                    "saldo_before_trx": "461381",<br>-->
<!--                                                    "saldo_after_trx": "431781",<br>-->
<!--                                                    "created_at": "2018-03-18 21:18:09",<br>-->
<!--                                                    "updated_at": "2018-03-18 21:18:32"<br>-->
<!--                                                }<br>-->
<!--                                            }<br>-->
                                            
<!--                                        </code>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                    </div>-->
<!--                        </div>-->
<!--                    </div>-->
<!--            </div>-->
<!--    </div>-->
<!--</div>-->

@endsection

@section('profile-js')
<script>
    function getApiKey() {
        $modal = $('#modal-api-key');
        
        $modal.find('.modal-body').prepend('<div class="alert alert-info">Memproses...</div>');
        
        var password = $modal.find('[name="password"]').val();
        $.ajax({
           url: "{{ route('profile.api_key') }}",
           type: "POST",
           dataType: "JSON",
           data: {
               password: password,
               _token: $('[name="csrf-token"]').attr('content')
           },
           success: function(s) {
               $modal.find('.alert').remove();
               if( s.success )
               {
                   $('#api_key').val(s.api_key);
                   $modal.modal('hide');
               }
               else
               {
                    $modal.find('.help-block').remove();
                    $modal.find('.form-group').removeClass('has-error');
                    $modal.find('.form-group').addClass('has-error');
                    $modal.find('.form-group').append('<p class="help-block"><small>' + s.message + '</small></p>');
               }
           },
           error: function(e) {
               $modal.find('.alert').remove();
               $modal.find('.help-block').remove();
               $modal.find('.form-group').removeClass('has-error');
               $modal.find('.form-group').addClass('has-error');
               $modal.find('.form-group').append('<p class="help-block"><small>Gagal memproses permintaan</small></p>');
           }
        });
    }
</script>
@endsection