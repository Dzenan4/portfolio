<?php

namespace App\Http\Controllers\Checks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Check;
use App\Http\Resources\CheckResource;

class CheckEditController extends Controller
{
    //
    public function __invoke(Check $check)
    {
        return inertia('checks/Edit', [
            'currentCheck' => new CheckResource($check),
        ]);
    }
}
