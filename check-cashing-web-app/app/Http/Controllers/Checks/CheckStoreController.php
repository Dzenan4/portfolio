<?php

namespace App\Http\Controllers\Checks;

use App\Models\Check;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CheckStoreController extends Controller
{
    //
    public function __invoke(Request $request) 
    {
        $data = $request->validate([
            'company_name' => 'required',
            'company_address_street' => '',
            'company_address_city' => '',
            'company_address_state' => '',
            'company_address_zip' => '',
            'account_number' => 'required',
            'routing_number' => 'required',
            'type' => '',
            'cashing_status' => '',
            'notes' => '',
        ]);

        $data['slug'] = str($data['company_name'] . ' ' . $data['account_number'])->slug();

        Check::create($data);

        return to_route('checks.index')->with('success', 'Check created successfully.');

    }
}
