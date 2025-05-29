<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Storage;

class CustomerDestroyController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Customer $customer)
    {
        if ($customer->dl_picture_link) {
            $dlPath = ltrim(str_replace('/storage/', '', $customer->dl_picture_link), '/');
            Storage::disk('public')->delete($dlPath);
        }
    
        if ($customer->self_picture_link) {
            $selfPath = ltrim(str_replace('/storage/', '', $customer->self_picture_link), '/');
            Storage::disk('public')->delete($selfPath);
        }
        
        $customer->delete();
        return to_route('customers.index')->with('success', 'Customer deleted successfully');
    }
}
