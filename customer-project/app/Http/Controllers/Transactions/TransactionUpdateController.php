<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;

class TransactionUpdateController extends Controller
{
    //
    public function __invoke(Request $request, Transaction $transaction)
    {
        $data = $request->validate([
            'customer_id' => 'required',
            'check_id' => 'required',
            'date' => '',
            'check_amount' => 'required',
            'payout_amount' => 'required',
            'charge_amount' => 'required',
            'charge_percentage' => 'required',
            'check_number' => 'required',
            'check_picture_link' => '',
        ]);

        $data['slug'] = str($data['customer_id'] . ' ' . $data['check_number'])->slug();

        $data['check_picture_link'] = $transaction->check_picture_link;
        
        if ($request->hasFile('check_picture_link')) {
            if (!empty($transaction->check_picture_link)) {
                Storage::disk('public')->delete($transaction->check_picture_link);
            }
            $data['check_picture_link'] = Storage::disk('public')->put('transaction_check_pictures', $request->file('check_picture_link'));
            $data['check_picture_link'] = Storage::url($data['check_picture_link']);
        }
        
        Transaction::update($data);

        return to_route('transactions.index')->with('success', 'Transaction created successfully.');
    }
}
