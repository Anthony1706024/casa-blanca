<?php

namespace App\Http\Controllers\Cajera;

use App\Http\Controllers\Controller;
use App\Http\Requests\PedidoPersonalRequest;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Recibo;
use App\Services\ComandaService;
use App\Services\TicketeraService;
use App\Services\VentaService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CobroController extends Controller
{
    // ==========================================
    // PEDIDOS POR COBRAR (búsqueda por DNI)
    // ==========================================
    public function index(Request $request)
    {
        $query = Pedido::with(['items', 'mozo'])->where('estado', Pedido::PENDIENTE)->orderBy('id');

        if ($request->filled('dni')) {
            $query->where('dni', 'LIKE', "%{$request->dni}%");
        }

        $pedidos = $query->paginate(15)->withQueryString();

        return view('cajera.pedidos.index', compact('pedidos'));
    }

    // ==========================================
    // NUEVO PEDIDO DESDE CAJA: se registra y pasa directo al cobro
    // ==========================================
    public function create()
    {
        $productos = Producto::disponiblesParaVenta();

        return view('cajera.pedidos.create', compact('productos'));
    }

    public function store(PedidoPersonalRequest $request, VentaService $ventas)
    {
        $datos = $request->validated();

        $pedido = $ventas->crearPedido($datos, $datos['items'], 'caja', $request->user());

        return redirect()->route('cajera.pedidos.show', $pedido->id)
            ->with('success', "Pedido {$pedido->numero} registrado. Ahora registra el cobro.");
    }

    // ==========================================
    // ADICIONALES: más productos para la misma mesa, en el mismo pedido
    // ==========================================
    public function adicionales($id)
    {
        $pedido = Pedido::with('items')->findOrFail($id);

        if ($pedido->estado !== Pedido::PENDIENTE) {
            return redirect()->route('cajera.pedidos.show', $pedido->id)
                ->with('error', 'Solo se pueden agregar productos a un pedido pendiente.');
        }

        $productos = Producto::disponiblesParaVenta();

        return view('cajera.pedidos.adicionales', compact('pedido', 'productos'));
    }

    public function adicionalesStore(Request $request, $id, VentaService $ventas)
    {
        $datos = $request->validate([
            'items' => 'required|array',
            'items.*' => 'integer|min:0|max:99',
        ], [
            'items.required' => 'Selecciona al menos un producto para agregar.',
        ]);

        $antes = Pedido::findOrFail($id)->total;
        $pedido = $ventas->agregarItems((int) $id, $datos['items']);
        $agregado = number_format((float) $pedido->total - (float) $antes, 2);

        return redirect()->route('cajera.pedidos.show', $pedido->id)
            ->with('success', "Adicionales agregados (S/ {$agregado}). Imprime el ticket para enviarlos a cocina.");
    }

    // Quitar una unidad (cantidad=1) o la línea completa (sin cantidad)
    public function quitarItem(Request $request, $id, $item, VentaService $ventas)
    {
        $request->validate(['cantidad' => 'nullable|integer|min:1|max:99']);

        [$pedido, $nombre, $quitadas, $enCocina] = $ventas->quitarItem((int) $id, (int) $item, $request->integer('cantidad') ?: null);

        $mensaje = "Se quitó {$quitadas} × {$nombre}. Nuevo total: S/ ".number_format((float) $pedido->total, 2).'.';
        if ($enCocina > 0) {
            $mensaje .= " Ya estaba en cocina: avisa que no lo preparen ({$enCocina}).";
        }

        return redirect()->route('cajera.pedidos.show', $pedido->id)->with('success', $mensaje);
    }

    // ==========================================
    // DETALLE DEL PEDIDO Y FORMULARIO DE COBRO
    // ==========================================
    public function show($id)
    {
        $pedido = Pedido::with(['items.producto', 'mozo', 'recibo'])->findOrFail($id);

        if ($pedido->recibo) {
            return redirect()->route('recibos.show', $pedido->recibo->id);
        }

        $metodos = Pago::METODOS;
        $porEnviar = app(ComandaService::class)->porEnviar($pedido);

        return view('cajera.pedidos.show', compact('pedido', 'metodos', 'porEnviar'));
    }

    public function cobrar(Request $request, $id, VentaService $ventas, TicketeraService $ticketera)
    {
        // El número de operación es opcional en todos los métodos.
        $datos = $request->validate([
            'pagos' => 'required|array|min:1|max:2',
            'pagos.*.metodo' => ['required', Rule::in(array_keys(Pago::METODOS))],
            'pagos.*.monto' => 'required|numeric|gt:0',
            'pagos.*.numero_operacion' => 'nullable|string|max:50',
        ], [
            'pagos.required' => 'Registra al menos un pago.',
            'pagos.max' => 'Se permiten como máximo 2 formas de pago.',
            'pagos.*.metodo.required' => 'Selecciona el tipo de transacción.',
            'pagos.*.monto.required' => 'Indica el monto del pago.',
            'pagos.*.monto.gt' => 'El monto debe ser mayor a cero.',
        ]);

        $recibo = $ventas->cobrar((int) $id, $datos['pagos'], $request->user());

        // Si falla la ticketera, el cobro ya quedó registrado: solo se avisa
        $redireccion = redirect()->route('recibos.show', $recibo->id)
            ->with('success', 'Cobro registrado correctamente');

        if ($ticketera->configurada()) {
            return $ticketera->imprimirVoucher($recibo)
                ? $redireccion->with('impreso', $ticketera->enCola() ? 'Voucher enviado a la ticketera.' : 'Voucher impreso en la ticketera.')
                : $redireccion->with('error', 'El cobro se registró, pero no se pudo imprimir el voucher. Revisa que la ticketera esté prendida y vuelve a imprimir.');
        }

        return $redireccion;
    }

    public function anular($id, VentaService $ventas)
    {
        $ventas->anular((int) $id);

        return redirect()->route('cajera.pedidos.index')
            ->with('success', 'Pedido anulado');
    }

    // ==========================================
    // RECIBOS EMITIDOS
    // ==========================================
    public function recibos(Request $request)
    {
        $query = Recibo::with(['pedido', 'pagos'])->orderBy('id', 'desc');

        if ($request->filled('dni')) {
            $query->whereHas('pedido', fn ($q) => $q->where('dni', 'LIKE', "%{$request->dni}%"));
        }

        $recibos = $query->paginate(20)->withQueryString();

        return view('cajera.recibos', compact('recibos'));
    }
}
