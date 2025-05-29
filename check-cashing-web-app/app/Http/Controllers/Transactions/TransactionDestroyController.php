<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;

class TransactionDestroyController extends Controller
{
    //
    public function __invoke(Transaction $transaction)
    {
        $transaction->delete();
        return to_route('transactions.index')->with('success', 'Transaction deleted successfully');
    }
}
