<?php

namespace App\Http\Controllers\Checks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Check;

class CheckDestroyController extends Controller
{
    //
    public function __invoke(Check $check)
    {
        $check->delete();
        return to_route('checks.index')->with('success', 'Check deleted successfully');
    }
}
