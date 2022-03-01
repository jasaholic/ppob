<?php

namespace App\Http\Controllers\Api\V1;

use Auth, Validator, DB, Exception, Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\AppModel\{
    Setting, V_pembelianproduk_enterprise, V_pembelianproduk_agen, V_pembelianproduk_personal, Pembayaranproduk, Pembayarankategori, Pembayaranoperator, Pembeliankategori, Pembelianoperator
};
use Carbon\Carbon;

class ProductController extends Controller
{
    public $personal_role   = 1;
    public $admin_role      = 2;
    public $agen_role       = 3;
    public $enterprise_role = 4;

    public function __construct()
    {
        $this->settings = Setting::first();
    }
    
    public function categoryPrabayar(Request $request)
    {
        $categories = Pembeliankategori::selectRaw('id, product_name as name, status');
        
        if( !empty($request->id) ) {
            $categories->where('id', intval($request->id));
        }
        
        $categories = $categories->get();
        
        if( $categories->count() == 0 ) {
            return response()->json([
                'success'   => false,
                'message'      => 'Kategori tidak ditemukan'
                ]);
        }
        
        return response()->json([
            'success'   => true,
            'data'      => $categories
            ]);
    }
    
    public function operatorPrabayar(Request $request)
    {
        $operators = Pembelianoperator::selectRaw('id, product_id as code, product_name as name, prefix, status, pembeliankategori_id as category_id');
        
        if( !empty($request->id) ) {
            $operators->where('id', intval($request->id));
        }
        
        if( !empty($request->category_id) ) {
            $operators->where('pembeliankategori_id', intval($request->category_id));
        }
        
        $operators = $operators->get();
        
        if( $operators->count() == 0 ) {
            return response()->json([
                'success'   => false,
                'message'      => 'Operator tidak ditemukan'
                ]);
        }
        
        return response()->json([
            'success'   => true,
            'data'      => $operators
            ]);
    }
    
    public function prabayar(Request $request)
    {
        $user = $request->user();
        
        switch( $user->roles[0]->id )
        {
            case $this->enterprise_role:
                $products = V_pembelianproduk_enterprise::selectRaw('id, product_id as code, product_name as name, `desc`, price, status, pembelianoperator_id as operator_id, pembeliankategori_id as category_id');
                break;
            
            
            case $this->agen_role:
                $products = V_pembelianproduk_agen::selectRaw('id, product_id as code, product_name as name, `desc`, price, status, pembelianoperator_id as operator_id, pembeliankategori_id as category_id');
                break;
                
            default:
                $products = V_pembelianproduk_personal::selectRaw('id, product_id as code, product_name as name, `desc`, price, status, pembelianoperator_id as operator_id, pembeliankategori_id as category_id');
                break;
        }
        
        if( !empty($request->id) ) {
            $products->where('id', intval($request->id));
        }
        
        if( !empty($request->code) ) {
            $products->where('product_id', $request->code);
        }
        
        if( !empty($request->category_id) ) {
            $products->where('pembeliankategori_id', intval($request->category_id));
        }
        
        if( !empty($request->operator_id) ) {
            $products->where('pembelianoperator_id', intval($request->operator_id));
        }
        
        $products = $products->get();
        
        if( $products->count() == 0 ) {
            return response()->json([
                'success'   => false,
                'message'      => 'Produk tidak ditemukan'
                ]);
        }
        
        return response()->json([
            'success'   => true,
            'data'      => $products
            ]);
    }
    
    public function categoryPascabayar(Request $request)
    {
        $categories = Pembayarankategori::selectRaw('id, product_name as name, status');
        
        if( !empty($request->id) ) {
            $categories->where('id', intval($request->id));
        }
        
        $categories = $categories->get();
        
        if( $categories->count() == 0 ) {
            return response()->json([
                'success'   => false,
                'message'      => 'Kategori tidak ditemukan'
                ]);
        }
        
        return response()->json([
            'success'   => true,
            'data'      => $categories
            ]);
    }
    
    public function operatorPascabayar(Request $request)
    {
        $operators = Pembayaranoperator::selectRaw('id, product_name as name, status, pembayarankategori_id as category_id');
        
        if( !empty($request->id) ) {
            $operators->where('id', intval($request->id));
        }
        
        if( !empty($request->category_id) ) {
            $operators->where('pembayarankategori_id', intval($request->category_id));
        }
        
        $operators = $operators->get();
        
        if( $operators->count() == 0 ) {
            return response()->json([
                'success'   => false,
                'message'      => 'Operator tidak ditemukan'
                ]);
        }
        
        return response()->json([
            'success'   => true,
            'data'      => $operators
            ]);
    }
    
    public function pascabayar(Request $request)
    {
        $products = Pembayaranproduk::selectRaw('id, code, product_name as name, price_markup as admin, status, pembayaranoperator_id as operator_id, pembayarankategori_id as category_id');
        
        if( !empty($request->id) ) {
            $products->where('id', intval($request->id));
        }
        
        if( !empty($request->code) ) {
            $products->where('code', $request->code);
        }
        
        if( !empty($request->category_id) ) {
            $products->where('pembayarankategori_id', intval($request->category_id));
        }
        
        if( !empty($request->operator_id) ) {
            $products->where('pembayaranoperator_id', intval($request->operator_id));
        }
        
        $products = $products->get();
        
        if( $products->count() == 0 ) {
            return response()->json([
                'success'   => false,
                'message'      => 'Produk tidak ditemukan'
                ]);
        }
        
        return response()->json([
            'success'   => true,
            'data'      => $products
            ]);
    }
}