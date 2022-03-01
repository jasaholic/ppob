<div class="panel-group">
    
    <!-- KATEGORI PASCABAYAR -->
    <div class="panel panel-default" style="border: 1px solid #ddd !important;">
        <div class="panel-heading" role="tab" style="height: 36px !important;">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" href="#panel-kategori-pascabayar" aria-expanded="false" aria-controls="panel-kategori-pascabayar" class="collapsed">
                    <b>KATEGORI PASCABAYAR</b>
                </a>
            </h4>
        </div>
        <div id="panel-kategori-pascabayar" class="panel-collapse collapse" role="tabpanel" aria-expanded="false" style="height: 0px;">
            <div class="panel-body">
                <p>API ini digunakan untuk mendapatkan daftar kategori produk pascabayar</p>
                <p>URL: <b>{{ url('/api/v1/product/pascabayar/category') }}</b><br/>Method: <b>GET</b></p>
                
                <h4><b>Contoh Request</b></h4>
                <pre class="api-code"><code>&lt?php

$apiKey = "masukkan api key anda disini";

$query = [
    'id'    => 19, // filter berdasarkan id kategori (tidak wajib)
];

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL             => "{{ url('/api/v1/product/pascabayar/category') }}?".http_build_query($query),
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
            "name": "PEMBAYARAN PLN PASCABAYAR",
            "status": 1 // 1 = aktif, 0 = tidak aktif
        },
        {
            "id": 20,
            "name": "PEMBAYARAN INTERNET PASCABAYAR",
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
    <!-- /KATEGORI PASCABAYAR -->
    
    <!-- OPERATOR PASCABAYAR -->
    <div class="panel panel-default" style="border: 1px solid #ddd !important;">
        <div class="panel-heading" role="tab" style="height: 36px !important;">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" href="#panel-operator-pascabayar" aria-expanded="false" aria-controls="panel-operator-pascabayar" class="collapsed">
                    <b>OPERATOR PASCABAYAR</b>
                </a>
            </h4>
        </div>
        <div id="panel-operator-pascabayar" class="panel-collapse collapse" role="tabpanel" aria-expanded="false" style="height: 0px;">
            <div class="panel-body">
                <p>API ini digunakan untuk mendapatkan daftar operator produk pascabayar</p>
                <p>URL: <b>{{ url('/api/v1/product/pascabayar/operator') }}</b><br/>Method: <b>GET</b></p>
                
                <h4><b>Contoh Request</b></h4>
                <pre class="api-code"><code>&lt?php

$apiKey = "masukkan api key anda disini";

$query = [
    'id'    => 19, // filter berdasarkan id operator (tidak wajib)
    'category_id'    => 20, // filter berdasarkan id kategori (tidak wajib)
];

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL             => "{{ url('/api/v1/product/pascabayar/operator') }}?".http_build_query($query),
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
            "name": "PLN PASCABAYAR",
            "status": 1, // 1 = aktif, 0 = tidak aktif
            "category_id": 1
        },
        {
            "id": 35,
            "name": "INTERNET PASCABAYAR",
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
    <!-- /OPERATOR PASCABAYAR -->
    
    <!-- PRODUK PASCABAYAR -->
    <div class="panel panel-default" style="border: 1px solid #ddd !important;">
        <div class="panel-heading" role="tab" style="height: 36px !important;">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" href="#panel-produk-pascabayar" aria-expanded="false" aria-controls="panel-produk-pascabayar" class="collapsed">
                    <b>PRODUK PASCABAYAR</b>
                </a>
            </h4>
        </div>
        <div id="panel-produk-pascabayar" class="panel-collapse collapse" role="tabpanel" aria-expanded="false" style="height: 0px;">
            <div class="panel-body">
                <p>API ini digunakan untuk mendapatkan daftar produk pascabayar</p>
                <p>URL: <b>{{ url('/api/v1/product/pascabayar') }}</b><br/>Method: <b>GET</b></p>
                
                <h4><b>Contoh Request</b></h4>
                <pre class="api-code"><code>&lt?php

$apiKey = "masukkan api key anda disini";

$query = [
    'id'    => 19, // filter berdasarkan id produk (tidak wajib)
    'code'    => "BPJS", // filter berdasarkan kode produk (tidak wajib)
    'operator_id'    => 1, // filter berdasarkan id operator (tidak wajib)
    'category_id'    => 20, // filter berdasarkan id kategori (tidak wajib)
];

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL             => "{{ url('/api/v1/product/pascabayar') }}?".http_build_query($query),
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
            "code": "BPJS",
            "name": "Pembayaran BPJS",
            "admin": 1500,
            "status": 1, // 1 = aktif, 0 = tidak aktif
            "operator_id": 2,
            "category_id": 1
        },
        {
            "id": 24,
            "code": "SPEEDY",
            "name": "Pembayaran Speedy",
            "admin": 1500,
            "status": 1, // 1 = aktif, 0 = tidak aktif
            "operator_id": 2,
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
    <!-- /PRODUK PASCABAYAR -->
    
</div>