<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Customer;
use App\Models\Check;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\CheckResource;
use Inertia\Inertia;
use Inertia\Response;


class TransactionIndexController extends Controller
{
    //
    public function __invoke(Request $request): Response
    {
        $customers = Customer::latest()->take(20)->get();

        $checks = Check::latest()->take(20)->get();

        $transactions = Transaction::latest()->get();
        return Inertia::render('transactions/Index', [
            'transactions' => TransactionResource::collection($transactions), 
            'customers' => CustomerResource::collection($customers),
            'checks' => CheckResource::collection($checks)
        ]);
    }

    public function getRecentTransactions(Request $request) {

        $customer_id = $request->input('customer_id');

        $transactions = Transaction::with(['check:id,company_name'])->where('customer_id', $customer_id)->orderBy('date', 'desc')->latest()->get();

        return $transactions;

    }
}
