<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Storage;

class TransactionStoreController extends Controller
{
    //
    public function __invoke(Request $request)
    {
       $data = $request->validate([
            'customer_id' => 'required',
            'check_id' => 'required',
            'date' => 'date|nullable',
            'check_amount' => 'required',
            'payout_amount' => 'required',
            'charge_amount' => '',
            'charge_percentage' => '',
            'check_number' => 'required',
            'check_picture_link' => '',
        ]);

        $data['slug'] = str($data['customer_id'] . ' ' . $data['check_number'])->slug();

        if ($request->hasFile('check_picture_link')) {
            $data['check_picture_link'] = Storage::disk('public')->put('transaction_check_pictures', $request->file('check_picture_link'));
            $data['check_picture_link'] = Storage::url($data['check_picture_link']);
        }

        Transaction::create($data);

        return to_route('transactions.index')->with('success', 'Transaction created successfully.');
    }
}