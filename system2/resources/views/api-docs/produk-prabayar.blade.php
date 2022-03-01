<div class="panel-group">
    
    <!-- KATEGORI PRABAYAR -->
    <div class="panel panel-default" style="border: 1px solid #ddd !important;">
        <div class="panel-heading" role="tab" style="height: 36px !important;">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" href="#panel-kategori-prabayar" aria-expanded="false" aria-controls="panel-kategori-prabayar" class="collapsed">
                    <b>KATEGORI PRABAYAR</b>
                </a>
            </h4>
        </div>
        <div id="panel-kategori-prabayar" class="panel-collapse collapse" role="tabpanel" aria-expanded="false" style="height: 0px;">
            <div class="panel-body">
                <p>API ini digunakan untuk mendapatkan daftar kategori produk prabayar</p>
                <p>URL: <b>{{ url('/api/v1/product/prabayar/category') }}</b><br/>Method: <b>GET</b></p>
                
                <h4><b>Contoh Request</b></h4>
                <pre class="api-code"><code>&lt?php

$apiKey = "masukkan api key anda disini";

$query = [
    'id'    => 19, // filter berdasarkan id kategori (tidak wajib)
];

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL             => "{{ url('/api/v1/product/prabayar/category') }}?".http_build_query($query),
    CURLOPT_RETURNTRANSFER  => true,
    CURLOPT_HEADER          => false,
    CURLOPT_HTTPHEADER      => [
        "Authorization: Bearer ".$apiKey
    ]
]);

$result = curl_exec($ch);
curl_close($ch);

echo $result;
            
?&gt</code></pre>

            <h4><b>Contoh Respon Sukses</b></h4>
            <pre class="api-code"><code>{
    "success": true,
    "data": [
        {
            "id": 19,
            "name": "PULSA ALL OPERATOR",
            "status": 1 // 1 = aktif, 0 = tidak aktif
        },
        {
            "id": 20,
            "name": "VOUCHER GAME",
            "status": 1 // 1 = aktif, 0 = tidak aktif
        }
    ]
}</code></pre>

            <h4><b>Contoh Respon Gagal</b></h4>
            <pre class="api-code"><code>{
    "success": false,
    "message": "Ini pesan errornya"
}</code></pre>
            
            </div>
        </div>
    </div>
    <!-- /KATEGORI PRABAYAR -->
    
    <!-- OPERATOR PRABAYAR -->
    <div class="panel panel-default" style="border: 1px solid #ddd !important;">
        <div class="panel-heading" role="tab" style="height: 36px !important;">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" href="#panel-operator-prabayar" aria-expanded="false" aria-controls="panel-operator-prabayar" class="collapsed">
                    <b>OPERATOR PRABAYAR</b>
                </a>
            </h4>
        </div>
        <div id="panel-operator-prabayar" class="panel-collapse collapse" role="tabpanel" aria-expanded="false" style="height: 0px;">
            <div class="panel-body">
                <p>API ini digunakan untuk mendapatkan daftar operator produk prabayar</p>
                <p>URL: <b>{{ url('/api/v1/product/prabayar/operator') }}</b><br/>Method: <b>GET</b></p>
                
                <h4><b>Contoh Request</b></h4>
                <pre class="api-code"><code>&lt?php

$apiKey = "masukkan api key anda disini";

$query = [
    'id'    => 19, // filter berdasarkan id operator (tidak wajib)
    'category_id'    => 20, // filter berdasarkan id kategori (tidak wajib)
];

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL             => "{{ url('/api/v1/product/prabayar/operator') }}?".http_build_query($query),
    CURLOPT_RETURNTRANSFER  => true,
    CURLOPT_HEADER          => false,
    CURLOPT_HTTPHEADER      => [
        "Authorization: Bearer ".$apiKey
    ]
]);

$result = curl_exec($ch);
curl_close($ch);

echo $result;
            
?&gt</code></pre>

            <h4><b>Contoh Respon Sukses</b></h4>
            <pre class="api-code"><code>{
    "success": true,
    "data": [
        {
            "id": 19,
            "code": "S",
            "name": "TELKOMSEL",
            "prefix": "0812,0853,0822",
            "status": 1, // 1 = aktif, 0 = tidak aktif
            "category_id": 1
        },
        {
            "id": 35,
            "code": "I",
            "name": "INDOSAT",
            "prefix": "0857,0815,0858",
            "status": 1, // 1 = aktif, 0 = tidak aktif
            "category_id": 1
        },
    ]
}</code></pre>

            <h4><b>Contoh Respon Gagal</b></h4>
            <pre class="api-code"><code>{
    "success": false,
    "message": "Ini pesan errornya"
}</code></pre>
            
            </div>
        </div>
    </div>
    <!-- /OPERATOR PRABAYAR -->
    
    <!-- PRODUK PRABAYAR -->
    <div class="panel panel-default" style="border: 1px solid #ddd !important;">
        <div class="panel-heading" role="tab" style="height: 36px !important;">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" href="#panel-produk-prabayar" aria-expanded="false" aria-controls="panel-produk-prabayar" class="collapsed">
                    <b>PRODUK PRABAYAR</b>
                </a>
            </h4>
        </div>
        <div id="panel-produk-prabayar" class="panel-collapse collapse" role="tabpanel" aria-expanded="false" style="height: 0px;">
            <div class="panel-body">
                <p>API ini digunakan untuk mendapatkan daftar produk prabayar</p>
                <p>URL: <b>{{ url('/api/v1/product/prabayar') }}</b><br/>Method: <b>GET</b></p>
                
                <h4><b>Contoh Request</b></h4>
                <pre class="api-code"><code>&lt?php

$apiKey = "masukkan api key anda disini";

$query = [
    'id'    => 19, // filter berdasarkan id produk (tidak wajib)
    'code'    => "S1", // filter berdasarkan kode produk (tidak wajib)
    'operator_id'    => 1, // filter berdasarkan id operator (tidak wajib)
    'category_id'    => 20, // filter berdasarkan id kategori (tidak wajib)
];

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL             => "{{ url('/api/v1/product/prabayar') }}?".http_build_query($query),
    CURLOPT_RETURNTRANSFER  => true,
    CURLOPT_HEADER          => false,
    CURLOPT_HTTPHEADER      => [
        "Authorization: Bearer ".$apiKey
    ]
]);

$result = curl_exec($ch);
curl_close($ch);

echo $result;
            
?&gt</code></pre>

            <h4><b>Contoh Respon Sukses</b></h4>
            <pre class="api-code"><code>{
    "success": true,
    "data": [
        {
            "id": 19,
            "code": "S1",
            "name": "TELKOMSEL 1.000",
            "desc": "Pulsa Telkomsel 1.000",
            "price": 1023,
            "status": 1, // 1 = aktif, 0 = tidak aktif
            "operator_id": 2,
            "category_id": 1
        },
        {
            "id": 24,
            "code": "I5",
            "name": "INDOSAT 5.000",
            "desc": "Pulsa Indosat 5.000",
            "price": 5200,
            "status": 1, // 1 = aktif, 0 = tidak aktif
            "operator_id": 3,
            "category_id": 1
        }
    ]
}</code></pre>

            <h4><b>Contoh Respon Gagal</b></h4>
            <pre class="api-code"><code>{
    "success": false,
    "message": "Ini pesan errornya"
}</code></pre>
            
            </div>
        </div>
    </div>
    <!-- /PRODUK PRABAYAR -->
    
</div>