<?php

namespace App\Http\Controllers\Mozo;

use App\Http\Controllers\Controller;
use App\Models\Pedido;

class DashboardController extends Controller
{
    public function index()
    {
        $pendientes = Pedido::where('estado', Pedido::PENDIENTE)->count();
        $misPedidosHoy = Pedido::where('mozo_id', auth()->id())->whereDate('created_at', today())->count();
        $pedidosCliente = Pedido::where('estado', Pedido::PENDIENTE)->where('origen', 'cliente')->count();

        return view('mozo.dashboard', compact('pendientes', 'misPedidosHoy', 'pedidosCliente'));
    }
}
