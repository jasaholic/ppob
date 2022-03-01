<div class="panel-group">
    <div class="panel panel-default" style="border: 1px solid #ddd !important;">
        <div class="panel-heading" role="tab" style="height: 36px !important;">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" href="#panel-info-saldo" aria-expanded="false" aria-controls="panel-info-saldo" class="collapsed">
                    <b>INFO SALDO</b>
                </a>
            </h4>
        </div>
        <div id="panel-info-saldo" class="panel-collapse collapse" role="tabpanel" aria-expanded="false" style="height: 0px;">
            <div class="panel-body">
                <p>API ini digunakan untuk mendapatkan informasi saldo akun</p>
                <p>URL: <b>{{ url('/api/v1/balance') }}</b><br/>Method: <b>GET</b></p>
                
                <h4><b>Contoh Request</b></h4>
                <pre class="api-code"><code>&lt?php

$apiKey = "masukkan api key anda disini";

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL             => "{{ url('/api/v1/balance') }}",
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
    "data": {
        "balance": "150000"
    }
}</code></pre>
            </div>
        </div>
    </div>
</div>