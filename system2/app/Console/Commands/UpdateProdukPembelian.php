<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Pulsa, DB,DigiFlazz,Exception ,Log;
use App\AppModel\Pembeliankategori;
use App\AppModel\Pembelianoperator;
use App\AppModel\Pembelianproduk;
use App\AppModel\Apiserver;
use App\AppModel\Prefix_phone;
class UpdateProdukPembelian extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'produkpembelian:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'FOR UPDATE PRODUCT';

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
        DB::statement("SET session wait_timeout=600");
        
        $products = DigiFlazz::daftarProduk();
        $products = json_decode($products);

        if( $products->success != true ) {
            return;
        }
        
        if( !is_object($products->response) ) {
            return;
        }

        foreach($products->response->data as $product)
        {
            usleep(10000);
            
            DB::beginTransaction();
    
            try
            {
                preg_match('/^[a-zA-Z]+/ui', $product->buyer_sku_code, $pref);
                $prefix = (count($pref) > 0 ? strtoupper($pref[0]) : null);
                
                if( empty($prefix) ) {
                    DB::rollBack();
                    continue;
                }

                switch($prefix)
                {
                    // untuk tambahkan operator produk nanti disini mas
                    case 'TSEL': // NAH PREFIX TSEL tadi di masukinn di switch case sini mas, nanti untuk nama operatornya terserah mas mau dibuat apa
                        $operator = 'TELKOMSEL'; // ini nama Operator mas?iya mas oke
                        break;
                    case 'PTSEL':
                        $operator = 'TELKOSMEL';
                        break;
                    case 'TFTSEL': 
                        $operator = 'TELKOMSEL PULSA TRANSFER';
                        break;
                    
                     
                        
                    case 'ISMS': 
                        $operator = 'INDOSAT ';
                        break;
                        case 'PITU': 
                        $operator = 'INDOSAT UNLIMITED';
                        break;
                    case 'ITEL': 
                        $operator = 'INDOSAT ';
                        break;
                        
                    case 'SAT': 
                        $operator = 'INDOSAT';
                        break;
                    case 'TFSAT': 
                        $operator = 'INDOSAT PULSA TRANSFER';
                        break;
                    case 'SATGIFT': 
                        $operator = 'INDOSAT PULSA GIFT';
                        break;
                    

                     case 'PAXIS': 
                        $operator = 'AXIS';
                        break;
                    case 'AX': 
                        $operator = 'AXIS';
                        break;
                    case 'TFAX': 
                        $operator = 'AXIS PULSA TRANSFER';
                        break;
                    
                    case "PAKETTLFON":
                        $operator = "PAKET TELFON";
                        break;
                        
                    case "PTTSEL": 
                        $operator = 'TELKOMSEL';
                        break;
                    
                    case 'SMART': 
                        $operator = 'SMARTFREN';
                        break;

                    case 'PSTRI': 
                        $operator = 'TRI';
                        break;
                    case 'PTTRI': 
                        $operator = 'TRI';
                        break;
                    case 'TRI': 
                        $operator = 'TRI';
                        break;
                    case 'TFTRI': 
                        $operator = 'TRI PULSA TRANSFER';
                        break;

                    case 'COMBOS': 
                        $operator = 'TELKOMSEL';
                        break;

                    case 'XL': 
                        $operator = 'XL';
                        break;
                    
                    case 'BYU': 
                        $operator = 'BY.U';
                        break; 
                    
                    case 'TSELDATAUMUM': 
                        $operator = 'TELKOMSEL Data Umum';
                        break;
                        
                  
                    case 'ADM': 
                        $operator = "AXIS Data Mini";
                        break;    
                    case 'VIDEOMAX': 
                        $operator = "TELKOMSEL Data Video Max";
                        break;

                    case 'RUANGGURU': 
                        $operator = "TELKOMSEL Data Ruang Guru";
                        break; 
                    case 'ILMUPEDIA': 
                        $operator = "TELKOMSEL Data Ilmupedia";
                        break; 
                    case 'TSELCOMBO': 
                        $operator = 'TELKOMSEL Data Combo';
                        break;
                    case 'BULK': 
                        $operator = "TELKOMSEL Data Bulk";
                        break;
                    case 'FLASH': 
                        $operator = "TELKOMSEL Data Flash";
                        break;
                    case 'MINI': 
                        $operator = "TELKOMSEL Data Mini";
                        break;
                    
                    case 'GARENA': 
                        $operator = "GARENA";
                        break;
                    case 'ML': 
                        $operator = "MOBILE LEGEND";
                        break;
                    case 'PB': 
                        $operator = 'POINT BLANK';
                        break;
                    case 'RR': 
                        $operator = "RAGNAROK M:ETERNAL LOVE";
                        break; 
                    case 'FF': 
                        $operator = 'FREE FIRE';
                        break;
                    case 'AOV': 
                        $operator = 'ARENA OF VALOR';
                        break;
                    case 'PUBGM':
                        $operator = "PUBG MOBILE";
                        break; 
                    case 'CODM': 
                        $operator = "CALL OF DUTY MOBILE";
                        break; 
                    case 'LM': 
                        $operator = "LAPLACE M";
                        break;
                    case 'LORDM': 
                        $operator = "LORDS MOBILE";
                        break;
                    case 'SD': 
                        $operator = "SPEED DRIFTERS";
                        break;
                    case 'SP': 
                        $operator = "STARPASS";
                        break;
                    case 'HD': 
                        $operator = "HIGGS DOMINO";
                        break;
                    case 'INDOP': 
                        $operator = "INDOPLAY";
                        break;
                    case 'HAGO': 
                        $operator = "HAGO";
                        break;
                    case 'VALO': 
                        $operator = 'VALORANT';
                        break;
                    
                     case 'ABR': 
                        $operator = 'AXIS Bronet';
                        break; 
                    case 'VT': 
                        $operator = 'VOUCHER TELKOMSEL DATA';
                        break; 
                    case 'VAIGO': 
                        $operator = 'VOUCHER AIGO AXIS';
                        break; 
                    case 'VSMARTFREN': 
                        $operator = "VOUCHER SMARTFREN DATA ";
                        break;
                    case 'VTRI': 
                        $operator = "VOUCHER TRI DATA";
                        break;
                    case 'VGPLAYI': 
                        $operator = "VOUCHER GOOGLE PLAY";
                        break;
                    case 'VGARENA': 
                        $operator = "VOUCHER GARENA";
                        break;
                    case 'VGS': 
                        $operator = "VOUCHER GEMSCOOL";
                        break;
                    case 'VMEGAXUS': 
                        $operator = 'VOUCHER MEGAXUS';
                        break;
                    case 'VPS': 
                        $operator = "VOUCHER PLAYSTATION";
                        break;
                    case 'STEAMW': 
                        $operator = "VOUCHER STEAM WALLET";
                        break;
                    case 'WAVE': 
                        $operator = "VOUCHER WAVE GAME";
                        break;
                    case 'WIFIID1': 
                        $operator = "VOUCHER WIFI ID 1";
                        break;
                    case 'WIFI2ID': 
                        $operator = "VOUCHER WIFI ID 2";
                        break;
                    case 'WIFIID3': 
                        $operator = "VOUCHER WIFI ID 3";
                        break;
                    case 'INDOMAR': 
                        $operator = "VOUCHER INDOMARET";
                        break;
                    case 'ANCOLTIKET': 
                        $operator = "VOUCHER ANCOL";
                        break;
                    case 'SPOTIFY': 
                        $operator = "VOUCHER SPOTIFY";
                        break;
                    case 'CARREFOUR': 
                        $operator = "VOUCHER CARREFOUR";
                        break;
                    case 'HNM': 
                        $operator = "VOUCHER H&M";
                        break;
                    case 'POINTB': 
                        $operator = "VOUCHER POINT BLANK";
                        break;
                    case 'PUBGPCBIRDKEY': 
                        $operator = "VOUCHER PUBG EARLY BIRD KEY";
                        break;
                    case 'PUBGSTEAMKEY': 
                        $operator = "VOUCHER PUBG STEAM GAME KEY";
                        break;

                  
                    case 'GOPAY': 
                        $operator = "GOPAY";
                        break;
                    case 'GOPAYDRIVER': // yang ini ya mas?
                        $operator = "GOPAY DRIVER";
                        break;
                    case 'GRABDRIVER':
                        $operator = "GRAB DRIVER";
                        break;
                    case 'SHOPEEPAY':
                        $operator = "SHOPEE PAY";
                        break;
                    case 'MANDIRIE': 
                        $operator = "MANDIRI E-TOLL";
                        break;
                    case 'OVO': 
                        $operator = "OVO";
                        break;
                    case 'GRAB': 
                        $operator = 'GRAB';
                        break;
                    case 'DANA': 
                        $operator = "DANA";
                        break;
                    case 'LINKAJA': 
                        $operator = "LINK AJA";
                        break;
                    case 'TAPCASHBNI': 
                        $operator = "TAPCASH BNI";
                        break;
                    case 'BRIBRIZZI': 
                        $operator = "BRI BRIZZI";
                        break;
                    case 'I.SAKU': 
                        $operator = "I.SAKU";
                        break;
                    case 'MAXIM': 
                        $operator = "MAXIM";
                        break;

                    case 'TOKEN': 
                        $operator = 'TOKEN PLN';
                        break;

                    case 'ORANGE': 
                        $operator = "ORANGE TV";
                        break;
                    case 'NEXGARUDA': 
                        $operator = "NEX & GARUDA TV";
                        break;
                    case 'KVISION': 
                        $operator = "KVISION TV";
                        break;
                    case 'DECODERG': 
                        $operator = "DECODER & GOL TV";
                        break;
                    case 'TRANSVISION': 
                        $operator = "TRANSVISION TV";
                        break;
                    
                        
                    default:
                        DB::rollBack();
                        continue 2;
                        break;
                }
                
                $next = false;
                //nah untuk nambahkan kategori disini mas
                switch(true)
                {
                    //jadi seumpaama mas nambahin e-money di digiflazznya 
                    # ===============================================================================================
                    # E-Toll
                    case in_array($prefix, ['MANDIRIE']):
                        $next = true;
                        $nameproduct = 'E-TOLL';
                        $savekategori  = Pembeliankategori::firstOrNew(
                            [
                                'slug'         => str_slug($nameproduct),
                            ]
                        );
                        
                        $savekategori->apiserver_id = 1;
                        $savekategori->product_name = $nameproduct;
                        $savekategori->type         = 'LAIN';
                        $savekategori->slug         = str_slug($nameproduct);
                        $savekategori->status       = 1;
                        $savekategori->jenis        = 'pembelian';
                        $savekategori->save();
                        break;
                    # ===============================================================================================
                     # E-MONEY
                    case in_array($prefix, ['COMBOS']):
                        $next = true;
                        $nameproduct = 'PAKET TELKOMSEL';
                        $savekategori  = Pembeliankategori::firstOrNew(
                            [
                                'slug'         => str_slug($nameproduct),
                            ]
                        );
                        
                        $savekategori->apiserver_id = 1;
                        $savekategori->product_name = $nameproduct;
                        $savekategori->type         = 'LAIN';
                        $savekategori->slug         = str_slug($nameproduct);
                        $savekategori->status       = 1;
                        $savekategori->jenis        = 'pembelian';
                        $savekategori->save();
                        break;

                    # ===============================================================================================
                     # E-MONEY
                    case in_array($prefix, ['PTSEL']):
                        $next = true;
                        $nameproduct = 'TELKOMSEL';
                        $savekategori  = Pembeliankategori::firstOrNew(
                            [
                                'slug'         => str_slug($nameproduct),
                            ]
                        );
                        
                        $savekategori->apiserver_id = 1;
                        $savekategori->product_name = $nameproduct;
                        $savekategori->type         = 'LAIN';
                        $savekategori->slug         = str_slug($nameproduct);
                        $savekategori->status       = 1;
                        $savekategori->jenis        = 'pembelian';
                        $savekategori->save();
                        break;

                    # ===============================================================================================
                    # E-MONEY
                    case in_array($prefix, ['SHOPEEPAY','GRABDRIVER']):
                        $next = true;
                        $nameproduct = 'SHOPEE PAY';
                        $savekategori  = Pembeliankategori::firstOrNew(
                            [
                                'slug'         => str_slug($nameproduct),
                            ]
                        );
                        
                        $savekategori->apiserver_id = 1;
                        $savekategori->product_name = $nameproduct;
                        $savekategori->type         = 'LAIN';
                        $savekategori->slug         = str_slug($nameproduct);
                        $savekategori->status       = 1;
                        $savekategori->jenis        = 'pembelian';
                        $savekategori->save();
                        break;

                    # ===============================================================================================
                    # E-MONEY
                    case in_array($prefix, ['GRABDRIVER']):
                        $next = true;
                        $nameproduct = 'GRAB DRIVER';
                        $savekategori  = Pembeliankategori::firstOrNew(
                            [
                                'slug'         => str_slug($nameproduct),
                            ]
                        );
                        
                        $savekategori->apiserver_id = 1;
                        $savekategori->product_name = $nameproduct;
                        $savekategori->type         = 'LAIN';
                        $savekategori->slug         = str_slug($nameproduct);
                        $savekategori->status       = 1;
                        $savekategori->jenis        = 'pembelian';
                        $savekategori->save();
                        break;
                    # ===============================================================================================
                    # E-MONEY
                    case in_array($prefix, ['GOPAYDRIVER']):
                        $next = true; // dan apabila mas mau buat operator baru tinnggal copy paste yang sudah ada dann sesuain dengankebutuhan mas
                        $nameproduct = 'GOPAY DRIVER';
                        $savekategori  = Pembeliankategori::firstOrNew(
                            [
                                'slug'         => str_slug($nameproduct),
                            ]
                        );
                        
                        $savekategori->apiserver_id = 1;
                        $savekategori->product_name = $nameproduct;
                        $savekategori->type         = 'LAIN';
                        $savekategori->slug         = str_slug($nameproduct);
                        $savekategori->status       = 1;
                        $savekategori->jenis        = 'pembelian';
                        $savekategori->save();
                        break;
                    # ===============================================================================================
    
                    # E-MONEY
                    case in_array($prefix, ['GOPAY', 'OVO', 'DANA', 'GRAB', 'LINKAJA', 'TAPCASHBNI', 'BRIBRIZZI', 'I.SAKU', 'MAXIM','SHOPEEPAY']):
                        $next = true; // dan apabila mas mau buat operator baru tinnggal copy paste yang sudah ada dann sesuain dengankebutuhan mas
                        $nameproduct = 'E-MONEY';
                        $savekategori  = Pembeliankategori::firstOrNew(
                            [
                                'slug'         => str_slug($nameproduct),
                            ]
                        );
                        
                        $savekategori->apiserver_id = 1;
                        $savekategori->product_name = $nameproduct;
                        $savekategori->type         = 'LAIN';
                        $savekategori->slug         = str_slug($nameproduct);
                        $savekategori->status       = 1;
                        $savekategori->jenis        = 'pembelian';
                        $savekategori->save();
                        break;
                    # ===============================================================================================
                    //SUDAH BENAR MAS?SUDAH MAS
                     # VOUCHER GAME
                     case in_array($prefix, ['GARENA']):
                        $next = true;
                        $nameproduct = 'VOUCHER GARENA';
                        $savekategori  = Pembeliankategori::firstOrNew(
                            [
                                'slug'         => str_slug($nameproduct),
                            ]
                        );
                        $savekategori->apiserver_id = 1;
                        $savekategori->product_name = $nameproduct;
                        $savekategori->type         = 'GAME';
                        $savekategori->slug         = str_slug($nameproduct);
                        $savekategori->status       = 1;
                        $savekategori->jenis        = 'pembelian';
                        $savekategori->save();
                        break;
                     # ===============================================================================================
                     # SMS
                     case in_array($prefix, ['PSTSEL', 'ISMS','PSTRI']):
                        $next = true;
                        $nameproduct = 'PAKET SMS ';
                        $savekategori  = Pembeliankategori::firstOrNew(
                            [
                                'slug'         => str_slug($nameproduct),
                            ]
                        );
                        $savekategori->apiserver_id = 1;
                        $savekategori->product_name = $nameproduct;
                        $savekategori->type         = 'SMS';
                        $savekategori->slug         = str_slug($nameproduct);
                        $savekategori->status       = 1;
                        $savekategori->jenis        = 'pembelian';
                        $savekategori->save();
                        break;
                     # ===============================================================================================
                    # TELFON
                     case in_array($prefix, ['PTTSEL','ITEL','PITU','PAXIS','PTTRI']):
                        $next = true;
                        $nameproduct = 'PAKET TELFON';
                        $savekategori  = Pembeliankategori::firstOrNew(
                            [
                                'slug'         => str_slug($nameproduct),
                            ]
                        );
                        $savekategori->apiserver_id = 1;
                        $savekategori->product_name = $nameproduct;
                        $savekategori->type         = 'PAKETTLFON';
                        $savekategori->slug         = str_slug($nameproduct);
                        $savekategori->status       = 1;
                        $savekategori->jenis        = 'pembelian';
                        $savekategori->save();
                        break;
                     # ===============================================================================================
                     # PUBG MOBILE UC
                     case in_array($prefix, ['PUBGM']):
                        $next = true;
                        $nameproduct = 'PUBG MOBILE UC';
                        $savekategori  = Pembeliankategori::firstOrNew(
                            [
                                'slug'         => str_slug($nameproduct),
                            ]
                        );
                        $savekategori->apiserver_id = 1;
                        $savekategori->product_name = $nameproduct;
                        $savekategori->type         = 'GAME';
                        $savekategori->slug         = str_slug($nameproduct);
                        $savekategori->status       = 1;
                        $savekategori->jenis        = 'pembelian';
                        $savekategori->save();
                        break;
                    # ===============================================================================================
                     # Voucher Contoh
                     /*case in_array($prefix, ['GARENA']):
                        $next = true;
                        $nameproduct = 'VOUCHER GAME';//seperti ini mas?iya mas
                        $savekategori  = Pembeliankategori::firstOrNew(
                            [
                                'slug'         => str_slug($nameproduct),
                            ]
                        );
                        $savekategori->apiserver_id = 1;
                        $savekategori->product_name = $nameproduct;
                        $savekategori->type         = 'VOUCHER GARENA';// untuk type nya hurufnya harus besar semua mas// oke teirmakasih mas hehe // save mas?bentar ma, untuk typenya 
                        // cuma ini mas kalo mau isi
                        //VOUCHER
                        //GAME
                        //PULSA, UNTUK TIPENYA CUMA INI MAS oke cuma ini, izin saya coba ya mas heheh, OK MAS
                        //INTERNET
                        //GAME
                        //LAIN
                        $savekategori->slug         = str_slug($nameproduct);
                        $savekategori->status       = 1;
                        $savekategori->jenis        = 'pembelian';
                        $savekategori->save();
                        break;*/
                    # ===============================================================================================
                    
                    # FF DIAMOND
                    case in_array($prefix, ['FF','FF']):
                        $next = true;
                        $nameproduct = 'FREE FIRE DIAMOND';
                        $savekategori  = Pembeliankategori::firstOrNew(
                            [
                                'slug'         => str_slug($nameproduct),
                            ]
                        );
                        $savekategori->apiserver_id = 1;
                        $savekategori->product_name = $nameproduct;
                        $savekategori->type         = 'GAME';
                        $savekategori->slug         = str_slug($nameproduct);
                        $savekategori->status       = 1;
                        $savekategori->jenis        = 'pembelian';
                        $savekategori->save();
                        break;
                    # ===============================================================================================

                    # PLN Prabayar
                    case in_array($prefix, ['TOKEN']):
                        $next = true;
                        $nameproduct = 'TOKEN LISTRIK';
                        $savekategori  = Pembeliankategori::firstOrNew(
                            [
                                'slug'         => str_slug($nameproduct),
                            ]
                        );
                        
                        $savekategori->apiserver_id = 1;
                        $savekategori->product_name = $nameproduct;
                        $savekategori->type         = 'PLN';
                        $savekategori->slug         = str_slug($nameproduct);
                        $savekategori->status       = 1;
                        $savekategori->jenis        = 'pembelian';
                        $savekategori->save();
                        break;
                    # ===============================================================================================
                    
                    # PULSA TRANSFER
                    case in_array($prefix, ['TFTSEL', 'TFSAT', 'SATGIFT', 'TFAX', 'TFTRI']):
                        $next = true;
                        $nameproduct = 'PULSA TRANSFER';
                        $savekategori  = Pembeliankategori::firstOrNew(
                            [
                                'slug'         => str_slug($nameproduct),
                            ]
                        );
                        
                        $savekategori->apiserver_id = 1;
                        $savekategori->product_name = $nameproduct;
                        $savekategori->type         = 'TRANSFER';
                        $savekategori->slug         = str_slug($nameproduct);
                        $savekategori->status       = 1;
                        $savekategori->jenis        = 'pembelian';
                        $savekategori->save();
                        break;
                    # ===============================================================================================
                    # VOUCHER
                    case in_array($prefix, ['VAIGO','PTTSEL']):
                        $next = true;
                        $nameproduct = 'VOUCHER AXIS';
                        $savekategori  = Pembeliankategori::firstOrNew(
                            [
                                'slug'         => str_slug($nameproduct),
                            ]
                        );
                        
                        $savekategori->apiserver_id = 1;
                        $savekategori->product_name = $nameproduct;
                        $savekategori->type         = 'INTERNET';
                        $savekategori->slug         = str_slug($nameproduct);
                        $savekategori->status       = 1;
                        $savekategori->jenis        = 'pembelian';
                        $savekategori->save();
                        break;
                    # ===============================================================================================
                    
                    # VOUCHER
                    case in_array($prefix, ['VT', 'VSMARTFREN', 'VTRI', 'VGPLAYI', 'VGARENA', 'VGS', 'VMEGAXUS', 'VPS', 'STEAMW', 'WAVE', 'INDOMAR', 'ANCOLTIKET', 'SPOTIFY', 'CARREFOUR', 'HNM']):
                        $next = true;
                        $nameproduct = 'VOUCHER';
                        $savekategori  = Pembeliankategori::firstOrNew(
                            [
                                'slug'         => str_slug($nameproduct),
                            ]
                        );
                        
                        $savekategori->apiserver_id = 1;
                        $savekategori->product_name = $nameproduct;
                        $savekategori->type         = 'LAIN';
                        $savekategori->slug         = str_slug($nameproduct);
                        $savekategori->status       = 1;
                        $savekategori->jenis        = 'pembelian';
                        $savekategori->save();
                        break;
                    # ===============================================================================================
                    
                        # Voucher Wifi id
                    case in_array($prefix, ['WIFIID1','WIFI2ID','WIFIID3']):
                        $next = true;
                        $nameproduct = 'WIFI ID';
                        $savekategori  = Pembeliankategori::firstOrNew(
                            [
                                'slug'         => str_slug($nameproduct),
                            ]
                        );
                        
                        $savekategori->apiserver_id = 1;
                        $savekategori->product_name = $nameproduct;
                        $savekategori->type         = 'INTERNET';
                        $savekategori->slug         = str_slug($nameproduct);
                        $savekategori->status       = 1;
                        $savekategori->jenis        = 'pembelian';
                        $savekategori->save();
                        break;
                    # ===============================================================================================
                    
                    
                    # VOUCHER GAME
                    case in_array($prefix, ['VGARENA', 'VGS', 'VMEGAXUS', 'VPS', 'STEAMW', 'WAVE','POINTB', 'PUBGPCBIRDKEY', 'PUBGSTEAMKEY','GARENA','ML','PB','RR','AOV','CODM','LM','LORDM','SD','SP','HD','INDOP','HAGO','VALO']):
                        $next = true;
                        $nameproduct = 'VOUCHER GAME';
                        $savekategori  = Pembeliankategori::firstOrNew(
                            [
                                'slug'         => str_slug($nameproduct),
                            ]
                        );
                        
                        $savekategori->apiserver_id = 1;
                        $savekategori->product_name = $nameproduct;
                        $savekategori->type         = 'GAME';
                        $savekategori->slug         = str_slug($nameproduct);
                        $savekategori->status       = 1;
                        $savekategori->jenis        = 'pembelian';
                        $savekategori->save();
                        break;
                    # ===============================================================================================
                    

                    # PAKET DATA
                    case in_array($prefix, ['TSELDATAUMUM', 'VIDEOMAX', 'RUANGGURU', 'ILMUPEDIA', 'DATATSEL', 'TSELCOMBO', 'BULK', 'FLASH', 'MINI','ADM','VAIGO','ABR']):
                        $next = true;
                        $nameproduct = 'PAKET DATA';
                        $savekategori  = Pembeliankategori::firstOrNew(
                            [
                                'slug'         => str_slug($nameproduct),
                            ]
                        );
                        
                        $savekategori->apiserver_id = 1;
                        $savekategori->product_name = $nameproduct;
                        $savekategori->type         = 'INTERNET';
                        $savekategori->slug         = str_slug($nameproduct);
                        $savekategori->status       = 1;
                        $savekategori->jenis        = 'pembelian';
                        $savekategori->save();
                        break;
                    # ===============================================================================================
                    

                    # PULSA REGULER
                    case in_array($prefix, ['PTSEL','TSEL','SAT','AX','SMART','TRI','XL','BYU']):
                        $next = true;
                        $nameproduct = 'PULSA REGULER';
                        $savekategori  = Pembeliankategori::firstOrNew(
                            [
                                'slug'         => str_slug($nameproduct),
                            ]
                        );
                        
                        $savekategori->apiserver_id = 1;
                        $savekategori->product_name = $nameproduct;
                        $savekategori->type         = 'REGULER';
                        $savekategori->slug         = str_slug($nameproduct);
                        $savekategori->status       = 1;
                        $savekategori->jenis        = 'pembelian';
                        $savekategori->save();
                        break;
                    # ===============================================================================================
                    
                    
                    
                    # Voucher TV
                    case in_array($prefix, ['KVISION', 'DECODERG', 'TRANSVISION', 'NEXGARUDA', 'ORANGE']):
                        $next = true;
                        $nameproduct = 'VOUCHER TV';
                        $savekategori  = Pembeliankategori::firstOrNew(
                            [
                                'slug'         => str_slug($nameproduct),
                            ]
                        );
                        
                        $savekategori->apiserver_id = 1;
                        $savekategori->product_name = $nameproduct;
                        $savekategori->type         = 'LAIN';
                        $savekategori->slug         = str_slug($nameproduct);
                        $savekategori->status       = 1;
                        $savekategori->jenis        = 'pembelian';
                        $savekategori->save();
                        break;
                    # ===============================================================================================
                    

                    # OTHER
                    default:
                        $next = false;
                        DB::rollBack();
                        continue 2;
                        break;
                }
                
                if( $next === true )
                {
                    $explodeToGetPrefix = explode(" ", strtoupper($operator));
                    $getPrefix = Prefix_phone::where('name', 'like', '%'.$explodeToGetPrefix[0].'%')->first();

                    $saveoperator  = Pembelianoperator::firstOrNew(
                        [
                            'product_id'           => $prefix,
                        ]
                    );
                
                    $saveoperator->apiserver_id         = 1;
                    $saveoperator->product_id           = $prefix;
                    $saveoperator->product_name         = $operator;
                    $saveoperator->prefix               = $getPrefix ? $getPrefix->prefix : NULL;
                    $saveoperator->status               = 1;
                    $saveoperator->pembeliankategori_id = $savekategori->id;
                    $saveoperator->save();
            
                    $saveproduk  = Pembelianproduk::firstOrNew(
                        [
                            'product_id'           => $product->buyer_sku_code,
                        ]
                    );
                    
                    $productStatus = 0;

                    if( ($product->buyer_product_status == true) && ($product->seller_product_status == true) && ($product->unlimited_stock == true || $product->stock > 0) ) {
                        $productStatus = 1;
                    }
                
                    $saveproduk->apiserver_id                 = 1;
                    $saveproduk->product_id                   = $product->buyer_sku_code;
                    $saveproduk->pembeliankategori_id         = $savekategori->id;
                    $saveproduk->pembelianoperator_id         = $saveoperator->id;
                    $saveproduk->product_name                 = strtoupper($product->product_name);
                    $saveproduk->desc                         = !empty($product->desc) ? $product->desc : NULL;
                    $saveproduk->price_default                = $product->price;
                    $saveproduk->price_markup                 = !empty($saveproduk->price_markup) ? $saveproduk->price_markup : 0;
                    $saveproduk->status                       = $productStatus;
                    $saveproduk->save();
                }
                
                DB::commit();
            }
            catch(Exception $e)
            {
                DB::rollback();
                
                \Log::error($e);
            }
        }
    }
}