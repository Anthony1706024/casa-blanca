<?php

namespace App\Http\Controllers\Mozo;

use App\Http\Controllers\Controller;
use App\Models\Pedido;

// El mozo solo ve sus propios pedidos; caja ve todos
class DashboardController extends Controller
{
    public function index()
    {
        $mios = fn () => Pedido::where('mozo_id', auth()->id());

        $pendientes = $mios()->where('estado', Pedido::PENDIENTE)->count();
        $misPedidosHoy = $mios()->whereDate('created_at', today())->count();
        $pagadosHoy = $mios()->where('estado', Pedido::PAGADO)->whereDate('updated_at', today())->count();

        return view('mozo.dashboard', compact('pendientes', 'misPedidosHoy', 'pagadosHoy'));
    }
}
