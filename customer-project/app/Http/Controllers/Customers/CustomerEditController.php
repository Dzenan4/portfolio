<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Http\Resources\CustomerResource;

class CustomerEditController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Customer $customer)
    {
        return inertia('customers/Edit', [
            'currentCustomer' => new CustomerResource($customer),
        ]);
    }
}
