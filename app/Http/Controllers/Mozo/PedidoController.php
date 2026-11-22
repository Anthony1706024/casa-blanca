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

    // Un pedido de otro mozo responde 404, como si no existiera
    private function miPedido(Request $request, $id): Pedido
    {
        return Pedido::with(['items.producto', 'mozo'])->where('mozo_id', $request->user()->id)->findOrFail($id);
    }

    public function show(Request $request, $id)
    {
        $pedido = $this->miPedido($request, $id);

        return view('mozo.pedidos.show', compact('pedido'));
    }

    // ==========================================
    // ADICIONALES Y CAMBIOS: el mozo agrega o quita platos de su pedido pendiente.
    // Los adicionales le aparecen a caja para que imprima en cocina solo lo nuevo.
    // ==========================================
    public function adicionales(Request $request, $id)
    {
        $pedido = $this->miPedido($request, $id);

        if ($pedido->estado !== Pedido::PENDIENTE) {
            return redirect()->route('mozo.pedidos.show', $pedido->id)
                ->with('error', 'Solo se pueden agregar productos a un pedido pendiente.');
        }

        $productos = Producto::disponiblesParaVenta();

        return view('pedidos.adicionales', ['pedido' => $pedido, 'productos' => $productos, 'prefijo' => 'mozo']);
    }

    public function adicionalesStore(Request $request, $id, VentaService $ventas)
    {
        $pedido = $this->miPedido($request, $id);
        $datos = $request->validate([
            'items' => 'required|array',
            'items.*' => 'integer|min:0|max:99',
        ], [
            'items.required' => 'Selecciona al menos un producto para agregar.',
        ]);

        $antes = $pedido->total;
        $pedido = $ventas->agregarItems($pedido->id, $datos['items']);
        $agregado = number_format((float) $pedido->total - (float) $antes, 2);

        return redirect()->route('mozo.pedidos.show', $pedido->id)
            ->with('success', "Adicionales agregados (S/ {$agregado}). Caja ya los ve para imprimirlos en cocina.");
    }

    public function quitarItem(Request $request, $id, $item, VentaService $ventas)
    {
        $pedido = $this->miPedido($request, $id);
        $request->validate(['cantidad' => 'nullable|integer|min:1|max:99']);

        [$pedido, $nombre, $quitadas, $enCocina] = $ventas->quitarItem($pedido->id, (int) $item, $request->integer('cantidad') ?: null);

        $mensaje = "Se quitó {$quitadas} × {$nombre}. Nuevo total: S/ ".number_format((float) $pedido->total, 2).'.';
        if ($enCocina > 0) {
            $mensaje .= " Ya estaba en cocina: avisa que no lo preparen ({$enCocina}).";
        }

        return redirect()->route('mozo.pedidos.show', $pedido->id)->with('success', $mensaje);
    }

    public function anular(Request $request, $id, VentaService $ventas)
    {
        $pedido = $this->miPedido($request, $id);
        $ventas->anular($pedido->id);

        return redirect()->route('mozo.pedidos.index')
            ->with('success', 'Pedido anulado');
    }
}
