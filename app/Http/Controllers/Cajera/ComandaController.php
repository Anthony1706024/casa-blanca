<?php

namespace App\Http\Controllers\Cajera;

use App\Http\Controllers\Controller;
use App\Models\Contenido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Services\ComandaService;
use App\Services\TicketeraService;
use Illuminate\Http\Request;

// Tickets para cocina: Cocina 1, Bebidas y tragos, Cocina 2 (uno tras otro, cada uno con su corte)
class ComandaController extends Controller
{
    public function imprimir(Request $request, $id, ComandaService $comandas, TicketeraService $ticketera)
    {
        $pedido = Pedido::with(['items.producto', 'mozo'])->findOrFail($id);

        if ($pedido->estado === Pedido::ANULADO) {
            return back()->with('error', 'Este pedido está anulado: no se envía a cocina.');
        }

        $lote = $comandas->lote($pedido, $request->boolean('todo'));

        if (empty($lote['tickets'])) {
            return back()->with('error', 'No hay productos nuevos para enviar a cocina. Usa «Reimprimir tickets» si necesitas otra copia.');
        }

        $cocinas = collect(array_keys($lote['tickets']))->map(fn ($cocina) => Producto::COCINAS[$cocina])->implode(', ');

        // Sin ticketera configurada se imprime desde el navegador
        if (! $ticketera->configurada()) {
            $comandas->marcarEnviado($lote);

            return redirect()->route('cajera.pedidos.comanda.ver', $pedido->id)->with('comanda', $lote);
        }

        if (! $ticketera->imprimirComanda($pedido, $lote)) {
            return back()->with('error', 'No se pudieron imprimir los tickets de cocina. Revisa que la ticketera esté prendida y con papel, y vuelve a intentarlo.');
        }

        $comandas->marcarEnviado($lote);

        return back()->with('success', "Tickets enviados a cocina: {$cocinas}.");
    }

    // Versión para imprimir desde el navegador cuando no hay ticketera configurada
    public function ver($id, ComandaService $comandas)
    {
        $pedido = Pedido::with(['items.producto', 'mozo'])->findOrFail($id);
        $lote = session('comanda') ?? $comandas->lote($pedido, true);
        $negocio = Contenido::valores();
        $ancho = $negocio['ticket_ancho'] === '58' ? '58' : '80';
        $recienEnviado = session()->has('comanda');

        return view('cajera.pedidos.comanda', compact('pedido', 'lote', 'ancho', 'recienEnviado'));
    }
}
