<?php

namespace App\Http\Controllers\Cajera;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Recibo;
use App\Services\ComandaService;

class DashboardController extends Controller
{
    public function index()
    {
        $idsPendientes = Pedido::where('estado', Pedido::PENDIENTE)->pluck('id');
        $pendientes = $idsPendientes->count();
        $porCobrar = Pedido::where('estado', Pedido::PENDIENTE)->sum('total');
        $cobradoHoy = Recibo::whereDate('created_at', today())->sum('total');
        $recibosHoy = Recibo::whereDate('created_at', today())->count();

        return view('cajera.dashboard', compact('pendientes', 'porCobrar', 'cobradoHoy', 'recibosHoy', 'idsPendientes') + ['adicionales' => app(ComandaService::class)->adicionalesPorImprimir()]);
    }
}
