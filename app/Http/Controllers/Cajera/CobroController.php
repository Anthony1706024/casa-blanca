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

        // Un solo buscador: DNI o número de mesa (el DNI es opcional)
        if ($request->filled('dni')) {
            $buscar = trim($request->dni);
            $query->where(fn ($q) => $q->where('dni', 'LIKE', "%{$buscar}%")->orWhere('mesa', $buscar));
        }

        $pedidos = $query->paginate(15)->withQueryString();
        $idsPendientes = Pedido::where('estado', Pedido::PENDIENTE)->pluck('id');
        $adicionales = app(ComandaService::class)->adicionalesPorImprimir();

        return view('cajera.pedidos.index', compact('pedidos', 'idsPendientes', 'adicionales'));
    }

    // ==========================================
    // NUEVO PEDIDO DESDE CAJA: solo se guarda; el cobro es aparte, cuando el cliente paga
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
            ->with('success', "Pedido {$pedido->numero} guardado (mesa {$pedido->mesa}). Imprime el ticket para cocina; el cobro se registra cuando el cliente pague.");
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

        return view('pedidos.adicionales', ['pedido' => $pedido, 'productos' => $productos, 'prefijo' => 'cajera']);
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

        // Recién aquí el pedido pasa a pagado (la cuenta para la mesa se imprime antes, sin cobrar)
        $recibo = $ventas->cobrar((int) $id, $datos['pagos'], $request->user());
        $recibo->load('pedido');

        // Se vuelve a la lista: el comprobante va a la ticketera o se abre aparte, sin perder la pantalla de caja
        $mensaje = "Cobro registrado: {$recibo->pedido->numero}".($recibo->pedido->mesa ? " (mesa {$recibo->pedido->mesa})" : '')
            .' por S/ '.number_format((float) $recibo->total, 2).'.';
        $redireccion = redirect()->route('cajera.pedidos.index');

        if (! $request->boolean('imprimir', true)) {
            return $redireccion->with('success', $mensaje)->with('recibo_id', $recibo->id);
        }

        if (! $ticketera->configurada()) {
            // Sin ticketera: la lista ofrece abrir el comprobante en otra ventana para imprimirlo
            return $redireccion->with('success', $mensaje)->with('recibo_id', $recibo->id)->with('recibo_imprimir', true);
        }

        // Si falla la ticketera, el cobro ya quedó registrado: solo se avisa
        return $ticketera->imprimirVoucher($recibo)
            ? $redireccion->with('success', $mensaje.($ticketera->enCola() ? ' Comprobante enviado a la ticketera.' : ' Comprobante impreso.'))->with('recibo_id', $recibo->id)
            : $redireccion->with('success', $mensaje)->with('recibo_id', $recibo->id)
                ->with('error', 'No se pudo imprimir el comprobante. Revisa que la ticketera esté prendida y reimprímelo desde «Recibos emitidos».');
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
