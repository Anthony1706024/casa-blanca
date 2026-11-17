<?php

namespace App\Http\Controllers\Mozo;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Producto;
use App\Services\VentaService;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $query = Pedido::with(['items', 'mozo'])->orderBy('id', 'desc');

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

    public function store(Request $request, VentaService $ventas)
    {
        $datos = $request->validate([
            'dni' => 'required|digits:8',
            'cliente_nombre' => 'nullable|string|max:150',
            'mesa' => 'nullable|string|max:20',
            'observacion' => 'nullable|string|max:255',
            'items' => 'required|array',
            'items.*' => 'integer|min:0|max:99',
        ], [
            'dni.required' => 'El DNI del cliente es obligatorio.',
            'dni.digits' => 'El DNI debe tener 8 dígitos.',
            'items.required' => 'Selecciona al menos un producto.',
        ]);

        $pedido = $ventas->crearPedido($datos, $datos['items'], 'mozo', $request->user());

        return redirect()->route('mozo.pedidos.index')
            ->with('success', "Pedido {$pedido->numero} registrado. El cliente puede pagar en caja con su DNI.");
    }

    public function anular($id, VentaService $ventas)
    {
        $ventas->anular((int) $id);

        return redirect()->route('mozo.pedidos.index')
            ->with('success', 'Pedido anulado');
    }
}
