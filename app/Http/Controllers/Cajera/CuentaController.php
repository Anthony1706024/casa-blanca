<?php

namespace App\Http\Controllers\Cajera;

use App\Http\Controllers\Controller;
use App\Models\Contenido;
use App\Models\Pedido;
use App\Services\TicketeraService;

// Cuenta para llevar a la mesa: se imprime ANTES de cobrar y no cambia el estado del pedido.
// El cliente la revisa, el mozo trae el dinero y recién entonces caja registra el cobro.
// Se abre en una ventana aparte (cajera/pedidos/show), así la pestaña del pedido no se pierde.
class CuentaController extends Controller
{
    public function ver($id, TicketeraService $ticketera)
    {
        $pedido = Pedido::with(['items', 'mozo'])->findOrFail($id);
        $negocio = Contenido::valores();
        $ancho = in_array(request('ancho'), ['58', '80'], true) ? request('ancho') : ($negocio['ticket_ancho'] === '58' ? '58' : '80');
        $recibo = null;
        $ticketera = $ticketera->configurada();

        return view('recibos.show', compact('pedido', 'recibo', 'negocio', 'ancho', 'ticketera'));
    }

    public function imprimir($id, TicketeraService $ticketera)
    {
        $pedido = Pedido::with(['items', 'mozo'])->findOrFail($id);
        $vista = redirect()->route('cajera.pedidos.cuenta', $pedido->id);

        if ($pedido->estado === Pedido::ANULADO) {
            return $vista->with('error', 'Este pedido está anulado: no se imprime la cuenta.');
        }

        if (! $ticketera->configurada()) {
            return $vista->with('imprimir', true);
        }

        return $ticketera->imprimirCuenta($pedido)
            ? $vista->with('impreso', $ticketera->enCola() ? 'Cuenta enviada a la ticketera.' : 'Cuenta impresa en la ticketera.')
            : $vista->with('error', 'No se pudo imprimir la cuenta. Revisa que la ticketera esté prendida y con papel, y vuelve a intentarlo.');
    }
}
