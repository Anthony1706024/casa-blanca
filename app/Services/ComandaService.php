<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Producto;
use Illuminate\Support\Collection;

// ==========================================
// TICKETS DE COCINA (comandas): un ticket por cada cocina de Producto::COCINAS,
// en ese orden. Por defecto lleva solo lo que aún no se envió, así un adicional
// no repite lo que la cocina ya está preparando.
// ==========================================
class ComandaService
{
    // Devuelve:
    //  'tickets'    => [cocina => [['cantidad' => n, 'nombre' => texto], ...]] solo las cocinas con algo
    //  'items'      => [pedido_item_id => cantidad que queda enviada al imprimir]
    //  'adicional'  => true si el pedido ya tenía algo enviado antes
    //  'reimpresion'=> true si se pidió todo el pedido otra vez
    public function lote(Pedido $pedido, bool $todo = false): array
    {
        $pedido->loadMissing('items.producto');
        $cocinas = array_fill_keys(array_keys(Producto::COCINAS), []);
        $items = [];

        foreach ($pedido->items as $item) {
            $cantidad = $todo ? (int) $item->cantidad : $item->por_enviar;
            if ($cantidad <= 0) {
                continue;
            }

            $cocina = $item->producto->cocina ?? Producto::COCINA_POR_DEFECTO;
            $cocina = array_key_exists($cocina, $cocinas) ? $cocina : Producto::COCINA_POR_DEFECTO;

            $cocinas[$cocina][] = ['cantidad' => $cantidad, 'nombre' => $item->producto_nombre];
            $items[$item->id] = (int) $item->cantidad;
        }

        return [
            'tickets' => array_filter($cocinas),
            'items' => $items,
            'adicional' => ! $todo && $pedido->items->contains(fn ($item) => $item->cantidad_enviada > 0),
            'reimpresion' => $todo,
        ];
    }

    // Marca como enviado lo que salió en el lote
    public function marcarEnviado(array $lote): void
    {
        foreach ($lote['items'] as $id => $cantidad) {
            PedidoItem::whereKey($id)->where('cantidad_enviada', '<', $cantidad)->update(['cantidad_enviada' => $cantidad]);
        }
    }

    // Adicionales de pedidos pendientes que todavía no salieron en un ticket de cocina: [item_id => pedido_id].
    // Caja los recibe como aviso para imprimirlos.
    public function adicionalesPorImprimir(): Collection
    {
        return PedidoItem::where('tipo', PedidoItem::ADICIONAL)
            ->whereColumn('cantidad_enviada', '<', 'cantidad')
            ->whereHas('pedido', fn ($q) => $q->where('estado', Pedido::PENDIENTE))
            ->pluck('pedido_id', 'id');
    }

    public function porEnviar(Pedido $pedido): int
    {
        return $pedido->items->sum(fn ($item) => $item->por_enviar);
    }
}
