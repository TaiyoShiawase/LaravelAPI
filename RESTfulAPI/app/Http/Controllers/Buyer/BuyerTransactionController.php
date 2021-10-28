<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Buyer;
use Illuminate\Http\Request;

class BuyerTransactionController extends ApiController
{
    public function __construct()
    {
       parent::__construct();

       $this->middleware('scope:read-general')->only(['index']);
       $this->middleware('can:view,buyer')->only(['index']);
    }

    public function index(Buyer $buyer)
    {   
        $transaction = $buyer->transactions;

        return $this->showAll($transaction);
    }
}
