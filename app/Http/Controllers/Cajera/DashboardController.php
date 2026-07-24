<?php

namespace App\Http\Controllers\Cajera;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('cajera.dashboard');
    }
}