<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Models\Buyer;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class BuyerController extends ApiController
{
    public function __construct()
    {
       parent::__construct();

       $this->middleware('scope:read-general')->only(['show']);
       $this->middleware('can:view,buyer')->only(['show']);
    }

    public function index()
    {
        $this->allowedAdminAction();

        $buyers = Buyer::has('transactions')->get();

        return $this->showAll($buyers);
    }

    public function show(Buyer $buyer)
    {
        // $buyer = Buyer::has('transactions')->findOrFail($buyer);

        return $this->showOne($buyer);
    }
}
