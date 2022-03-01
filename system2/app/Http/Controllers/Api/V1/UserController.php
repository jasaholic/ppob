<?php

namespace App\Http\Controllers\Api\V1;

use Auth, Validator, Log, DB, Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\AppModel\{
    Mutasi
};

class UserController extends Controller
{
    public function balance(Request $request)
    {
        return response()->json([
            'success'   => true,
            'data'      => [
                'balance'   => $request->user()->saldo
            ]
        ]);
    }
    
}