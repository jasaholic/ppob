<?php

namespace App\Console\Commands;

use DB, Exception, Pulsa, DigiFlazz,Log;
use Illuminate\Console\Command;
use App\AppModel\Pembayarankategori;
use App\AppModel\Pembayaranoperator;
use App\AppModel\Pembayaranproduk;
use App\AppModel\Apiserver;
use App\AppModel\Prefix_phone;

class UpdateProdukPembayaran extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'produkpembayaran:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update produk pembayaran';

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

        $products = DigiFlazz::produkPascaBayar();
        $products = json_decode($products);
        
        if( $products->success !== true ) {
            return;
        }
        
        if( !is_object($products->response) ) {
            return;
        }
        
        foreach($products->response->data as $product)
        {
            DB::beginTransaction();

            try
            {
                switch(strtoupper($product->brand))
                {
                    case 'PDAM':
                        $type = "PDAM";
                        break;
                    
                    case 'PLN PASCABAYAR':
                        $type = "PLN";
                        break;
                        
                    case 'HP PASCABAYAR':
                        $type = "HP";
                        break;
                    
                    case 'INTERNET PASCABAYAR':
                        $type = "INTERNET";
                        break;
                        
                    case 'BPJS KESEHATAN':
                        $type = "BPJS";
                        break;
                        
                    case 'MULTIFINANCE':
                        $type = "MULTIFINANCE";
                        break;
                        
                    case 'GAS NEGARA':
                        $type = "GAS";
                        break;
                        
                    case 'PBB':
                        $type ="PBB";
                        break;
                        
                    case 'TV PASCABAYAR':
                        $type = "TV";
                        break;    
                        
                    case 'TOKEN':
                        $type = "TOKEN";
                        break;
                        
                    default:
                        DB::rollBack();
                        continue 2;
                        break;
                }
                $product_name = 'PEMBAYARAN '.$product->brand;
                $kategory   = Pembayarankategori::updateOrCreate(
                    ['product_name'=>$product_name],
                    [
                        'apiserver_id'=>1,
                        'product_name'=>$product_name,
                        'slug'=>str_slug($product_name),
                        'type'=>$type,
                        'status'=>1,
                        'jenis'=>'pembayaran',
                    ]  
                );
                $product_id = explode(' ',$product->brand);
                $operator = Pembayaranoperator::updateOrCreate(
                        ['product_name' => $product_name],
                        [
                            'apiserver_id' => 1,
                            'product_name' => $product_name,
                            'pembayarankategori_id' => $kategory->id,
                            'status'=>1,
                        ]
                    );
                    
                $produkpembayaran = Pembayaranproduk::firstOrNew([
                        'code' => $product->buyer_sku_code
                    ]);

                $markupServer = intval($product->admin) - intval($product->commission);
                $markupServer = $markupServer > 0 ? $markupServer : 0;
                
                $markupLocal = intval($produkpembayaran->markup);

                $produkpembayaran->apiserver_id = 1;
                $produkpembayaran->pembayarankategori_id = $kategory->id;
                $produkpembayaran->pembayaranoperator_id = $operator->id;
                $produkpembayaran->code = $product->buyer_sku_code;
                $produkpembayaran->product_name = $product->product_name;
                $produkpembayaran->price_default = $markupServer;
                $produkpembayaran->markup = $markupLocal;
                $produkpembayaran->price_markup = $markupServer + $markupLocal;
                $produkpembayaran->status = ($product->buyer_product_status === true && $product->seller_product_status === true) ? 1 : 0;
                $produkpembayaran->save();
                
                DB::commit();
            }
            catch(Exception $e)
            {
                DB::rollback();
                
                Log::error($e);
            }
        }
    }
}