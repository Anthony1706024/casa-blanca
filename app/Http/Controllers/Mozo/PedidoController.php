<?php

namespace App\Http\Controllers\Mozo;

use App\Http\Controllers\Controller;
use App\Http\Requests\PedidoPersonalRequest;
use App\Models\Pedido;
use App\Models\Producto;
use App\Services\VentaService;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    // Cada mozo ve y anula solo sus propios pedidos; caja ve todos
    public function index(Request $request)
    {
        $query = Pedido::with(['items', 'mozo'])->where('mozo_id', $request->user()->id)->orderBy('id', 'desc');

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('dni')) {
            $query->where('dni', 'LIKE', "%{$request->dni}%");
        }

        $pedidos = $query->paginate(15)->withQueryString();

        return view('mozo.pedidos.index', compact('pedidos'));
    }

    public function create()
    {
        $productos = Producto::disponiblesParaVenta();

        return view('mozo.pedidos.create', compact('productos'));
    }

    public function store(PedidoPersonalRequest $request, VentaService $ventas)
    {
        $datos = $request->validated();

        $pedido = $ventas->crearPedido($datos, $datos['items'], 'mozo', $request->user());

        return redirect()->route('mozo.pedidos.index')
            ->with('success', "Pedido {$pedido->numero} registrado para la mesa {$pedido->mesa}.");
    }

    public function anular(Request $request, $id, VentaService $ventas)
    {
        // Un pedido de otro mozo responde 404, como si no existiera
        $pedido = Pedido::where('mozo_id', $request->user()->id)->findOrFail($id);
        $ventas->anular($pedido->id);

        return redirect()->route('mozo.pedidos.index')
            ->with('success', 'Pedido anulado');
    }
}
