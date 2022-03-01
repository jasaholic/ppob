<div class="panel-group">
    
    <!-- ORDER PRODUK PRABAYAR -->
    <div class="panel panel-default" style="border: 1px solid #ddd !important;">
        <div class="panel-heading" role="tab" style="height: 36px !important;">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" href="#panel-order-produk-prabayar" aria-expanded="false" aria-controls="panel-order-produk-prabayar" class="collapsed">
                    <b>ORDER PRODUK PRABAYAR</b>
                </a>
            </h4>
        </div>
        <div id="panel-order-produk-prabayar" class="panel-collapse collapse" role="tabpanel" aria-expanded="false" style="height: 0px;">
            <div class="panel-body">
                <div class="alert alert-warning">PERHATIAN! Transaksi yang masuk akan melalui proses antrian sehingga Anda perlu melakukan pengecekan menggunakan API DETAIL TRANSAKSI PRABAYAR untuk mengecek status transaksi apakah sukses atau gagal</div>
                <p>API ini digunakan untuk melakukan transaksi pembelian produk prabayar</p>
                <p>URL: <b>{{ url('/api/v1/transaction/prabayar/create') }}</b><br/>Method: <b>POST</b></p>
                
                <h4><b>Contoh Request</b></h4>
                <pre class="api-code"><code>&lt?php

$apiKey = "masukkan api key anda disini";

$data = [
    "code"          => "S1", // kode produk
    "target"        => "081234567890", // nomor tujuan,
    "pin"           => "1111", // PIN transaksi
    "id_pelanggan"  => "111111111", // id pelanggan (khusus transaksi token listrik)
];

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL             => "{{ url('/api/v1/transaction/prabayar/create') }}",
    CURLOPT_RETURNTRANSFER  => true,
    CURLOPT_HEADER          => false,
    CURLOPT_HTTPHEADER      => [
        "Authorization: Bearer ".$apiKey
    ],
    CURLOPT_POST            => true,
    CURLOPT_POSTFIELDS      => http_build_query($data)
]);

$result = curl_exec($ch);
curl_close($ch);

echo $result;
            
?&gt</code></pre>

            <h4><b>Contoh Respon Sukses</b></h4>
            <pre class="api-code"><code>{
    "success": true,
    "message": "Transaksi anda berhasil diantrikan",
    "data": {
        "trx_id": 1547
    }
}</code></pre>

            <h4><b>Contoh Respon Gagal</b></h4>
            <pre class="api-code"><code>{
    "success": false,
    "message": "PIN salah"
}</code></pre>

            </div>
        </div>
    </div>
    <!-- /ORDER PRODUK PRABAYAR -->
    
    <!-- DETAIL TRANSAKSI PRABAYAR -->
    <div class="panel panel-default" style="border: 1px solid #ddd !important;">
        <div class="panel-heading" role="tab" style="height: 36px !important;">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" href="#panel-detail-transaksi-prabayar" aria-expanded="false" aria-controls="panel-detail-transaksi-prabayar" class="collapsed">
                    <b>DETAIL TRANSAKSI PRABAYAR</b>
                </a>
            </h4>
        </div>
        <div id="panel-detail-transaksi-prabayar" class="panel-collapse collapse" role="tabpanel" aria-expanded="false" style="height: 0px;">
            <div class="panel-body">
                <p>API ini digunakan untuk mendapatkan rincian transaksi pembelian produk prabayar</p>
                <p>URL: <b>{{ url('/api/v1/transaction/prabayar/detail/{trx_id}') }}</b><br/>Method: <b>GET</b></p>
                
                <h4><b>Contoh Request</b></h4>
                <pre class="api-code"><code>&lt?php

$apiKey = "masukkan api key anda disini";

$trx_id = 1547; // id transaksi (wajib)

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL             => "{{ url('/api/v1/transaction/prabayar/detail') }}/".$trx_id,
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
    "message": "Transaksi ditemukan",
    "data": {
        "trx_id": "1547",
        "via": "API",
        "code": "S1",
        "produk": "TELKOMSEL 1.000",
        "price": "1200",
        "target": "081234567890",
        "mtrpln": null,
        "note": "Trx s1 081234567890 Sukses. SN : 221753xxxxxxxxx",
        "token": "221753xxxxxxxxx",
        "status": "1", // 0 = pending, 1 = sukses, 2 = gagal
        "saldo_before_trx": "2000",
        "saldo_after_trx": "800",
        "created_at": "2020-08-18 21:16:04",
        "updated_at": "2020-08-18 21:30:57"
    }
}</code></pre>

            <h4><b>Contoh Respon Gagal</b></h4>
            <pre class="api-code"><code>{
    "success": false,
    "message": "Transaksi tidak ditemukan"
}</code></pre>

            </div>
        </div>
    </div>
    <!-- /DETAIL TRANSAKSI PRABAYAR -->
    
    <!-- RIWAYAT TRANSAKSI PRABAYAR -->
    <div class="panel panel-default" style="border: 1px solid #ddd !important;">
        <div class="panel-heading" role="tab" style="height: 36px !important;">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" href="#panel-riwayat-transaksi-prabayar" aria-expanded="false" aria-controls="panel-riwayat-transaksi-prabayar" class="collapsed">
                    <b>RIWAYAT TRANSAKSI PRABAYAR</b>
                </a>
            </h4>
        </div>
        <div id="panel-riwayat-transaksi-prabayar" class="panel-collapse collapse" role="tabpanel" aria-expanded="false" style="height: 0px;">
            <div class="panel-body">
                <p>API ini digunakan untuk mendapatkan daftar riwayat transaksi pembelian produk prabayar (maks. 1000 data per request)</p>
                <p>URL: <b>{{ url('/api/v1/transaction/prabayar/history') }}</b><br/>Method: <b>GET</b></p>
                
                <h4><b>Contoh Request</b></h4>
                <pre class="api-code"><code>&lt?php

$apiKey = "masukkan api key anda disini";

$query = [
    'trx_id'    => 12345, // filter berdasarkan id transaksi (tidak wajib)
    'date_start' => '2020-08-17 00:00:00', // filter berdasarkan tanggal awal transaksi (tidak wajib)
    'date_end' => '2020-08-17 23:59:59', // filter berdasarkan tanggal akhir transaksi (tidak wajib)
];

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL             => "{{ url('/api/v1/transaction/prabayar/history') }}?".http_build_query($query),
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

            <h4><b>Contoh Respon</b></h4>
            <pre class="api-code"><code>{
    "success": true,
    "data": [
        {
            "trx_id": "1546",
            "via": "API",
            "code": "S1",
            "produk": "TELKOMSEL 1.000",
            "price": "1200",
            "target": "081234567890",
            "mtrpln": null,
            "note": "Trx s1 081234567890 Sukses. SN : 221753xxxxxxxxx",
            "token": "221753xxxxxxxxx",
            "status": "1", // 0 = pending, 1 = sukses, 2 = gagal
            "saldo_before_trx": "2000",
            "saldo_after_trx": "800",
            "created_at": "2020-08-18 21:16:04",
            "updated_at": "2020-08-18 21:30:57"
        },
        {
            "trx_id": "1547",
            "via": "API",
            "code": "S1",
            "produk": "TELKOMSEL 1.000",
            "price": "1200",
            "target": "081234567890",
            "mtrpln": null,
            "note": "Trx s1 081234567890 Sukses. SN : 221753xxxxxxxxx",
            "token": "221753xxxxxxxxx",
            "status": "1", // 0 = pending, 1 = sukses, 2 = gagal
            "saldo_before_trx": "2000",
            "saldo_after_trx": "800",
            "created_at": "2020-08-18 21:16:04",
            "updated_at": "2020-08-18 21:30:57"
        },
    ]
}</code></pre>

            </div>
        </div>
    </div>
    <!-- /RIWAYAT TRANSAKSI PRABAYAR -->
    
    <!-- CEK TAGIHAN PASCABAYAR -->
    <div class="panel panel-default" style="border: 1px solid #ddd !important;">
        <div class="panel-heading" role="tab" style="height: 36px !important;">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" href="#panel-cek-tagihan-pascabayar" aria-expanded="false" aria-controls="panel-cek-tagihan-pascabayar" class="collapsed">
                    <b>CEK TAGIHAN PASCABAYAR</b>
                </a>
            </h4>
        </div>
        <div id="panel-cek-tagihan-pascabayar" class="panel-collapse collapse" role="tabpanel" aria-expanded="false" style="height: 0px;">
            <div class="panel-body">
                <p>API ini digunakan untuk melakukan pengecekan tagihan pascabayar sebelum melakukan pembayaran</p>
                <p>URL: <b>{{ url('/api/v1/transaction/pascabayar/check') }}</b><br/>Method: <b>POST</b></p>
                
                <h4><b>Contoh Request</b></h4>
                <pre class="api-code"><code>&lt?php

$apiKey = "masukkan api key anda disini";

$data = [
    "code"          => "PLN", // kode produk
    "id_pelanggan"  => "111111111", // id pelanggan
    "phone"         => "081234567890", // nomor hp pembeli
    "pin"           => "1111", // PIN transaksi
];

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL             => "{{ url('/api/v1/transaction/pascabayar/check') }}",
    CURLOPT_RETURNTRANSFER  => true,
    CURLOPT_HEADER          => false,
    CURLOPT_HTTPHEADER      => [
        "Authorization: Bearer ".$apiKey
    ],
    CURLOPT_POST            => true,
    CURLOPT_POSTFIELDS      => http_build_query($data)
]);

$result = curl_exec($ch);
curl_close($ch);

echo $result;
            
?&gt</code></pre>

            <h4><b>Contoh Respon Sukses</b></h4>
            <pre class="api-code"><code>{
    "success": true,
    "message": "Berhasil melakukan pengecekan tagihan",
    "data": {
        "trx_id": 50,
        "via": "API",
        "code": "PLN",
        "product_name": "Pembayaran PLN",
        "nama": "Nama Pelanggan",
        "periode": "202008",
        "jumlah_tagihan": "100000",
        "admin": "1250",
        "jumlah_bayar": "101250",
        "status": 0, // 0 = menunggu pembayaran, 1 = dalam proses, 2 = berhasil, 3 = gagal,
        "created_at": "2020-08-18 21:16:04",
        "updated_at": "2020-08-18 21:30:57"
    }
}</code></pre>

            <h4><b>Contoh Respon Gagal</b></h4>
            <pre class="api-code"><code>{
    "success": false,
    "message": "Tagihan belum tersedia"
}</code></pre>

            </div>
        </div>
    </div>
    <!-- /CEK TAGIHAN PASCABAYAR -->
    
    <!-- BAYAR TAGIHAN PASCABAYAR -->
    <div class="panel panel-default" style="border: 1px solid #ddd !important;">
        <div class="panel-heading" role="tab" style="height: 36px !important;">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" href="#panel-bayar-tagihan-pascabayar" aria-expanded="false" aria-controls="panel-bayar-tagihan-pascabayar" class="collapsed">
                    <b>BAYAR TAGIHAN PASCABAYAR</b>
                </a>
            </h4>
        </div>
        <div id="panel-bayar-tagihan-pascabayar" class="panel-collapse collapse" role="tabpanel" aria-expanded="false" style="height: 0px;">
            <div class="panel-body">
                <p>API ini digunakan untuk melakukan pembayaran tagihan pascabayar setelah melakukan pengecekan</p>
                <p>URL: <b>{{ url('/api/v1/transaction/pascabayar/pay') }}</b><br/>Method: <b>POST</b></p>
                
                <h4><b>Contoh Request</b></h4>
                <pre class="api-code"><code>&lt?php

$apiKey = "masukkan api key anda disini";

$data = [
    "trx_id"        => "50", // id transaksi dari pengecekan tagihan
    "pin"           => "1111", // PIN transaksi
];

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL             => "{{ url('/api/v1/transaction/pascabayar/pay') }}",
    CURLOPT_RETURNTRANSFER  => true,
    CURLOPT_HEADER          => false,
    CURLOPT_HTTPHEADER      => [
        "Authorization: Bearer ".$apiKey
    ],
    CURLOPT_POST            => true,
    CURLOPT_POSTFIELDS      => http_build_query($data)
]);

$result = curl_exec($ch);
curl_close($ch);

echo $result;
            
?&gt</code></pre>

            <h4><b>Contoh Respon Sukses</b></h4>
            <pre class="api-code"><code>{
    "success": true,
    "message": "Transaksi berhasil diproses"
}</code></pre>

            <h4><b>Contoh Respon Gagal</b></h4>
            <pre class="api-code"><code>{
    "success": false,
    "message": "Transaksi kadaluarsa atau sudah terbayar"
}</code></pre>

            </div>
        </div>
    </div>
    <!-- /BAYAR TAGIHAN PASCABAYAR -->
    
    <!-- DETAIL TRANSAKSI PASCABAYAR -->
    <div class="panel panel-default" style="border: 1px solid #ddd !important;">
        <div class="panel-heading" role="tab" style="height: 36px !important;">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" href="#panel-detail-transaksi-pascabayar" aria-expanded="false" aria-controls="panel-detail-transaksi-pascabayar" class="collapsed">
                    <b>DETAIL TRANSAKSI PASCABAYAR</b>
                </a>
            </h4>
        </div>
        <div id="panel-detail-transaksi-pascabayar" class="panel-collapse collapse" role="tabpanel" aria-expanded="false" style="height: 0px;">
            <div class="panel-body">
                <p>API ini digunakan untuk mendapatkan rincian transaksi pascabayar</p>
                <p>URL: <b>{{ url('/api/v1/transaction/pascabayar/detail/{trx_id}') }}</b><br/>Method: <b>GET</b></p>
                
                <h4><b>Contoh Request</b></h4>
                <pre class="api-code"><code>&lt?php

$apiKey = "masukkan api key anda disini";

$trx_id = 50;

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL             => "{{ url('/api/v1/transaction/pascabayar/detail') }}/".$trx_id,
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
    "message": "Transaksi ditemukan",
    "data": {
        "trx_id": 50,
        "via": "API",
        "code": "PLN",
        "product_name": "Pembayaran PLN",
        "no_pelanggan": "11111111111",
        "nama": "Nama Pelanggan",
        "periode": "202008",
        "jumlah_tagihan": "100000",
        "admin": "1250",
        "jumlah_bayar": "101250",
        "status": 0, // 0 = menunggu pembayaran, 1 = dalam proses, 2 = berhasil, 3 = gagal,
        "token": "87465985937587590",
        "created_at": "2020-08-18 21:16:04",
        "updated_at": "2020-08-18 21:30:57"
    }
}</code></pre>

            <h4><b>Contoh Respon Gagal</b></h4>
            <pre class="api-code"><code>{
    "success": false,
    "message": "Transaksi tidak ditemukan"
}</code></pre>

            </div>
        </div>
    </div>
    <!-- /DETAIL TRANSAKSI PASCABAYAR -->
    
    <!-- RIWAYAT TRANSAKSI PASCABAYAR -->
    <div class="panel panel-default" style="border: 1px solid #ddd !important;">
        <div class="panel-heading" role="tab" style="height: 36px !important;">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" href="#panel-riwayat-transaksi-pascabayar" aria-expanded="false" aria-controls="panel-riwayat-transaksi-pascabayar" class="collapsed">
                    <b>RIWAYAT TRANSAKSI PASCABAYAR</b>
                </a>
            </h4>
        </div>
        <div id="panel-riwayat-transaksi-pascabayar" class="panel-collapse collapse" role="tabpanel" aria-expanded="false" style="height: 0px;">
            <div class="panel-body">
                <p>API ini digunakan untuk mendapatkan daftar riwayat transaksi pascabayar</p>
                <p>URL: <b>{{ url('/api/v1/transaction/pascabayar/history') }}</b><br/>Method: <b>GET</b></p>
                
                <h4><b>Contoh Request</b></h4>
                <pre class="api-code"><code>&lt?php

$apiKey = "masukkan api key anda disini";

$query = [
    'trx_id'    => 12345, // filter berdasarkan id transaksi (tidak wajib)
    'date_start' => '2020-08-17 00:00:00', // filter berdasarkan tanggal awal transaksi (tidak wajib)
    'date_end' => '2020-08-17 23:59:59', // filter berdasarkan tanggal akhir transaksi (tidak wajib)
];

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL             => "{{ url('/api/v1/transaction/pascabayar/history') }}?".http_build_query($query),
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

            <h4><b>Contoh Respon</b></h4>
            <pre class="api-code"><code>{
    "success": true,
    "data": [
        {
            "trx_id": 50,
            "via": "API",
            "code": "PLN",
            "product_name": "Pembayaran PLN",
            "no_pelanggan": "11111111111",
            "nama": "Nama Pelanggan",
            "periode": "202008",
            "jumlah_tagihan": "100000",
            "admin": "1250",
            "jumlah_bayar": "101250",
            "status": 0, // 0 = menunggu pembayaran, 1 = dalam proses, 2 = berhasil, 3 = gagal,
            "token": "87465985937587590",
            "created_at": "2020-08-18 21:16:04",
            "updated_at": "2020-08-18 21:30:57"
        },
        {
            "trx_id": 51,
            "via": "API",
            "code": "PLN",
            "product_name": "Pembayaran PLN",
            "no_pelanggan": "11111111111",
            "nama": "Nama Pelanggan",
            "periode": "202008",
            "jumlah_tagihan": "100000",
            "admin": "1250",
            "jumlah_bayar": "101250",
            "status": 0, // 0 = menunggu pembayaran, 1 = dalam proses, 2 = berhasil, 3 = gagal,
            "token": "87465985937587590",
            "created_at": "2020-08-18 21:16:04",
            "updated_at": "2020-08-18 21:30:57"
        }
    ]
}</code></pre>

            </div>
        </div>
    </div>
    <!-- /RIWAYAT TRANSAKSI PASCABAYAR -->
    
</div>