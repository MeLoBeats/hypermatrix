<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class LogController extends Controller
{
    public function index()
    {
        return Inertia::render('Logs');
    }
}
