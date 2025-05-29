<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Storage;

class CustomerUpdateController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Customer $customer)
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
            'dl_picture_link' => 'nullable|image',
            'self_picture_link' => 'nullable|image',
            'notes' => '',
        ]);
        
        $data['slug'] = str($data['first_name'] . ' ' . $data['last_name'])->slug();
        $data['dl_picture_link'] = $customer->dl_picture_link;
        $data['self_picture_link'] = $customer->self_picture_link;
        
        if ($request->hasFile('dl_picture_link')) {
            if (!empty($customer->dl_picture_link)) {
                Storage::disk('public')->delete($customer->dl_picture_link);
            }
            $data['dl_picture_link'] = Storage::disk('public')->put('customer_dl_pictures', $request->file('dl_picture_link'));
            $data['dl_picture_link'] = Storage::url($data['dl_picture_link']);
        }
        
        if ($request->hasFile('self_picture_link')) {
            if (!empty($customer->self_picture_link)) {
                Storage::disk('public')->delete($customer->self_picture_link);
            }
            $data['self_picture_link'] = Storage::disk('public')->put('customer_self_pictures', $request->file('self_picture_link'));
            $data['self_picture_link'] = Storage::url($data['self_picture_link']);
        }
        
        $customer->update($data);
        
        return to_route('customers.index')->with('success', 'Customer updated successfully.');
    }
}
