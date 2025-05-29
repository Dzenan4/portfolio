<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Customer;
use App\Http\Resources\CustomerResource;

class CustomerIndexController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): Response
    {
        $customers = Customer::latest()->get();
        return Inertia::render('customers/Index', [
            'customers' => CustomerResource::collection($customers),
        ]);
    }
}
