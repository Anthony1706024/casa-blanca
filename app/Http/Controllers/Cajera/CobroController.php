<?php

namespace App\Http\Controllers\Cajera;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\Recibo;
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
    // DETALLE DEL PEDIDO Y FORMULARIO DE COBRO
    // ==========================================
    public function show($id)
    {
        $pedido = Pedido::with(['items', 'mozo', 'recibo'])->findOrFail($id);

        if ($pedido->recibo) {
            return redirect()->route('recibos.show', $pedido->recibo->id);
        }

        $metodos = Pago::METODOS;

        return view('cajera.pedidos.show', compact('pedido', 'metodos'));
    }

    public function cobrar(Request $request, $id, VentaService $ventas, TicketeraService $ticketera)
    {
        // El número de operación es obligatorio salvo en efectivo.
        $datos = $request->validate([
            'pagos' => 'required|array|min:1|max:2',
            'pagos.*.metodo' => ['required', Rule::in(array_keys(Pago::METODOS))],
            'pagos.*.monto' => 'required|numeric|gt:0',
            'pagos.*.numero_operacion' => 'nullable|string|max:50|required_unless:pagos.*.metodo,efectivo',
        ], [
            'pagos.required' => 'Registra al menos un pago.',
            'pagos.max' => 'Se permiten como máximo 2 formas de pago.',
            'pagos.*.metodo.required' => 'Selecciona el tipo de transacción.',
            'pagos.*.monto.required' => 'Indica el monto del pago.',
            'pagos.*.monto.gt' => 'El monto debe ser mayor a cero.',
            'pagos.*.numero_operacion.required_unless' => 'El número de operación es obligatorio (excepto en efectivo).',
        ]);

        $recibo = $ventas->cobrar((int) $id, $datos['pagos'], $request->user());

        // Si falla la ticketera, el cobro ya quedó registrado: solo se avisa
        $redireccion = redirect()->route('recibos.show', $recibo->id)
            ->with('success', 'Cobro registrado correctamente');

        if ($ticketera->configurada()) {
            return $ticketera->imprimirVoucher($recibo)
                ? $redireccion->with('impreso', 'Voucher impreso en la ticketera.')
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
