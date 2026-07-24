<?php

namespace App\Http\Controllers\Mozo;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('mozo.dashboard');
    }
}