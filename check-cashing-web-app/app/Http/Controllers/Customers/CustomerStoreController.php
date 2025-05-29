<?php

namespace App\Http\Controllers\Customers;

use App\Models\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomerStoreController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required',
            'middle_inital' => '',
            'last_name' => 'required',
            'address_street' => 'required',
            'address_city' => 'required',
            'address_state' => 'required',
            'address_zip' => 'required',
            'phone_number' => '',
            'dl_number' => '',
            'dl_state' => '',
            'dob' => 'date|after_or_equal:1900-01-01|nullable',
            'dl_picture_link' => '',
            'self_picture_link' => '',
            'notes' => '',
        ]);
        
        $data['slug'] = str($data['first_name'] . ' ' . $data['last_name'])->slug();

        if ($request->hasFile('dl_picture_link')) {
            $data['dl_picture_link'] = Storage::disk('public')->put('customer_dl_pictures', $request->file('dl_picture_link'));
            $data['dl_picture_link'] = Storage::url($data['dl_picture_link']);
        }

        if ($request->hasFile('self_picture_link')) {
            $data['self_picture_link'] = Storage::disk('public')->put('customer_self_pictures', $request->file('self_picture_link'));
            $data['self_picture_link'] = Storage::url($data['self_picture_link']);
        }

        Customer::create($data);

        return to_route('customers.index')->with('success', 'Customer created successfully.');
    }
}
