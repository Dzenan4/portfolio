<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionCreateController extends Controller
{
    //
    public function __invoke(): Response
    {
        return Inertia::render('transactions/Create');
    }
}
