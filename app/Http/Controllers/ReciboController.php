<?php

namespace App\Http\Controllers;

use App\Models\Contenido;
use App\Models\Recibo;
use App\Services\TicketeraService;
use Illuminate\Support\Facades\DB;

class ReciboController extends Controller
{
    public function show($id)
    {
        $recibo = Recibo::with(['pedido.items', 'pedido.mozo', 'pagos', 'cajero'])->findOrFail($id);
        $negocio = Contenido::valores();

        // ?ancho=58 permite probar otro papel sin cambiar la configuración
        $ancho = in_array(request('ancho'), ['58', '80'], true) ? request('ancho') : ($negocio['ticket_ancho'] === '58' ? '58' : '80');

        $ticketera = app(TicketeraService::class)->configurada();

        return view('recibos.show', compact('recibo', 'negocio', 'ancho', 'ticketera'));
    }

    // Borra el recibo con sus pagos y el pedido con sus platos, de la lista y de la base.
    // Sirve para limpiar ventas de prueba. No devuelve stock (a propósito).
    public function destroy($id)
    {
        $recibo = Recibo::with('pedido')->findOrFail($id);
        $numeros = "{$recibo->numero} y su pedido {$recibo->pedido->numero}";

        DB::transaction(function () use ($recibo) {
            $pedido = $recibo->pedido;
            $recibo->delete();          // los pagos se borran en cascada
            $pedido->delete();          // los platos del pedido se borran en cascada
        });

        return back()->with('success', "Se borró el recibo {$numeros}.");
    }

    // Reimprimir en la ticketera
    public function imprimir($id, TicketeraService $ticketera)
    {
        $recibo = Recibo::findOrFail($id);

        return $ticketera->imprimirVoucher($recibo)
            ? back()->with('impreso', 'Voucher enviado a la ticketera.')
            : back()->with('error', 'No se pudo imprimir. Revisa que la ticketera esté conectada, prendida y con papel.');
    }
}
