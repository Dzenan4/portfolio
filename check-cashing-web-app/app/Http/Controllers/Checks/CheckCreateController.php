<?php

namespace App\Http\Controllers\Checks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckCreateController extends Controller
{
    //
    public function __invoke(): Response
    {
        return Inertia::render('checks/Create');
    }
}
