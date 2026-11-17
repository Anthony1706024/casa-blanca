<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Insumo;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Recibo;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $usuarios = User::count();
        $productos = Producto::count();
        $insumos = Insumo::count();
        $insumosBajos = Insumo::whereColumn('stock', '<=', 'stock_minimo')->count();
        $ventasHoy = Recibo::whereDate('created_at', today())->sum('total');
        $pedidosPendientes = Pedido::where('estado', Pedido::PENDIENTE)->count();

        return view('admin.dashboard', compact('usuarios', 'productos', 'insumos', 'insumosBajos', 'ventasHoy', 'pedidosPendientes'));
    }
}
