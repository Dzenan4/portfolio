<?php

namespace App\Http\Controllers\Checks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Check;
use App\Http\Resources\CheckResource;

class CheckIndexController extends Controller
{
    //
    public function __invoke(): Response
    {
        $checks = Check::latest()->get();
        return Inertia::render('checks/Index', [
            'checks' => CheckResource::collection($checks),
        ]);
    }
}
