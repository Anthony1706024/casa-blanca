<?php

namespace App\Http\Controllers\Cajera;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Recibo;

class DashboardController extends Controller
{
    public function index()
    {
        $pendientes = Pedido::where('estado', Pedido::PENDIENTE)->count();
        $porCobrar = Pedido::where('estado', Pedido::PENDIENTE)->sum('total');
        $cobradoHoy = Recibo::whereDate('created_at', today())->sum('total');
        $recibosHoy = Recibo::whereDate('created_at', today())->count();

        return view('cajera.dashboard', compact('pendientes', 'porCobrar', 'cobradoHoy', 'recibosHoy'));
    }
}
