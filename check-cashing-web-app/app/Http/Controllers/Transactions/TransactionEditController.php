<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Http\Resources\TransactionResource;

class TransactionEditController extends Controller
{
    //
    public function __invoke(Transaction $transaction)
    {
        return inertia('transactions/Edit', [
            'currentTransaction' => new TransactionResource($transaction),
        ]);
    }
}
