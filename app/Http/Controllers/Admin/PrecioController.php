<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\PedidoItem;
use App\Models\Producto;
use App\Models\ProductoPrecio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrecioController extends Controller
{
    // ==========================================
    // GESTIONAR PRECIOS
    // ==========================================
    public function index(Request $request)
    {
        $query = Producto::with('categoria');

        if ($request->filled('search')) {
            $query->where('nombre', 'LIKE', "%{$request->search}%");
        }

        if ($request->filled('categoria')) {
            $query->where('categoria_id', $request->categoria);
        }

        $productos = $query->orderBy('nombre')->paginate(30)->withQueryString();
        $categorias = Categoria::orderBy('nombre')->get();

        return view('admin.precios.index', compact('productos', 'categorias'));
    }

    public function actualizar(Request $request)
    {
        $request->validate([
            'precios' => 'required|array',
            'precios.*' => 'nullable|numeric|min:0|max:99999999',
            'destacados' => 'nullable|array',
            'descripciones' => 'nullable|array',
            'descripciones.*' => 'nullable|string|max:1000',
        ], [
            'precios.*.numeric' => 'Los precios deben ser números.',
            'precios.*.min' => 'Un precio no puede ser negativo.',
        ]);

        $destacados = array_keys($request->input('destacados', []));
        $cambios = 0;

        DB::transaction(function () use ($request, $destacados, &$cambios) {
            foreach (Producto::whereKey(array_keys($request->precios))->get() as $producto) {
                // El historial lo registra el modelo al detectar el cambio de precio
                $producto->precio_venta = $request->precios[$producto->id];
                $precioCambio = $producto->isDirty('precio_venta');
                $producto->destacado = in_array($producto->id, $destacados);
                if (array_key_exists($producto->id, $request->input('descripciones', []))) {
                    $producto->descripcion = $request->descripciones[$producto->id];
                }
                $producto->save();
                $cambios += $precioCambio ? 1 : 0;
            }
        });

        return redirect()->back()
            ->with('success', "Guardado. Precios cambiados: {$cambios}. Las ventas ya registradas conservan su precio original.");
    }

    // ==========================================
    // HISTORIAL DE CAMBIOS DE PRECIO
    // ==========================================
    public function historial(Request $request)
    {
        $query = ProductoPrecio::with(['producto', 'usuario'])->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $query->whereHas('producto', fn ($q) => $q->where('nombre', 'LIKE', "%{$request->search}%"));
        }

        $cambios = $query->paginate(30)->withQueryString();

        return view('admin.precios.historial', compact('cambios'));
    }

    // ==========================================
    // VENTAS POR PRECIO: cuánto se vendió realmente a cada precio
    // ==========================================
    public function ventas(Request $request)
    {
        $query = PedidoItem::query()
            ->join('recibos', 'recibos.pedido_id', '=', 'pedido_items.pedido_id')
            ->leftJoin('productos', 'productos.id', '=', 'pedido_items.producto_id');

        if ($request->filled('search')) {
            $query->where('pedido_items.producto_nombre', 'LIKE', "%{$request->search}%");
        }

        if ($request->filled('desde')) {
            $query->whereDate('recibos.created_at', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $query->whereDate('recibos.created_at', '<=', $request->hasta);
        }

        $total = (clone $query)->sum('pedido_items.subtotal');

        $filas = $query
            ->selectRaw('pedido_items.producto_id, pedido_items.producto_nombre, pedido_items.precio_unitario, productos.precio_venta as precio_actual')
            ->selectRaw('SUM(pedido_items.cantidad) as unidades, SUM(pedido_items.subtotal) as importe')
            ->selectRaw('MIN(recibos.created_at) as primera_venta, MAX(recibos.created_at) as ultima_venta')
            ->groupBy('pedido_items.producto_id', 'pedido_items.producto_nombre', 'pedido_items.precio_unitario', 'productos.precio_venta')
            ->orderBy('pedido_items.producto_nombre')
            ->orderBy('primera_venta')
            ->paginate(30)
            ->withQueryString();

        return view('admin.precios.ventas', compact('filas', 'total'));
    }
}
