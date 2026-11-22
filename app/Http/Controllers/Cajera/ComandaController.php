<?php

namespace App\Http\Controllers\Cajera;

use App\Http\Controllers\Controller;
use App\Models\Contenido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Services\ComandaService;
use App\Services\TicketeraService;
use Illuminate\Http\Request;

// Tickets para cocina: Cocina 1, Bebidas y tragos, Cocina 2 (uno tras otro, cada uno con su corte).
// Se abren en una ventana aparte (cajera/pedidos/show), así la pestaña del pedido no se recarga:
// toda respuesta termina en la vista del ticket, desde donde se imprime o reimprime.
class ComandaController extends Controller
{
    public function imprimir(Request $request, $id, ComandaService $comandas, TicketeraService $ticketera)
    {
        $pedido = Pedido::with(['items.producto', 'mozo'])->findOrFail($id);
        $vista = redirect()->route('cajera.pedidos.comanda.ver', $pedido->id);

        if ($pedido->estado === Pedido::ANULADO) {
            return $vista->with('error', 'Este pedido está anulado: no se envía a cocina.');
        }

        $lote = $comandas->lote($pedido, $request->boolean('todo'));

        if (empty($lote['tickets'])) {
            return $vista->with('error', 'No hay productos nuevos para enviar a cocina. Usa «Reimprimir» si necesitas otra copia.');
        }

        $cocinas = collect(array_keys($lote['tickets']))->map(fn ($cocina) => Producto::COCINAS[$cocina])->implode(', ');

        // Sin ticketera configurada se imprime desde el navegador
        if (! $ticketera->configurada()) {
            $comandas->marcarEnviado($lote);

            return $vista->with('comanda', $lote);
        }

        if (! $ticketera->imprimirComanda($pedido, $lote)) {
            return $vista->with('error', 'No se pudieron imprimir los tickets de cocina. Revisa que la ticketera esté prendida y con papel, y vuelve a intentarlo.');
        }

        $comandas->marcarEnviado($lote);

        return $vista->with('comanda', $lote)->with('success', "Tickets enviados a cocina: {$cocinas}.");
    }

    // Vista del ticket: muestra lo último que se envió, o lo pendiente si falta enviar algo
    public function ver($id, ComandaService $comandas, TicketeraService $ticketera)
    {
        $pedido = Pedido::with(['items.producto', 'mozo'])->findOrFail($id);
        $porEnviar = $comandas->porEnviar($pedido);
        $lote = session('comanda') ?? $comandas->lote($pedido, $porEnviar === 0);
        $negocio = Contenido::valores();
        $ancho = $negocio['ticket_ancho'] === '58' ? '58' : '80';
        $conTicketera = $ticketera->configurada();
        // Sin ticketera, lo recién enviado se imprime solo desde el navegador
        $recienEnviado = ! $conTicketera && session()->has('comanda');

        return view('cajera.pedidos.comanda', compact('pedido', 'lote', 'ancho', 'recienEnviado', 'porEnviar', 'conTicketera'));
    }
}
