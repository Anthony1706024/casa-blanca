<?php

namespace App\Services;

use App\Models\Insumo;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Recibo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VentaService
{
    // ==========================================
    // REGISTRAR PEDIDO (no descuenta stock, solo verifica que alcance)
    // $cantidades = [producto_id => cantidad]
    // ==========================================
    public function crearPedido(array $datos, array $cantidades, string $origen, ?User $mozo = null): Pedido
    {
        $cantidades = array_filter(array_map('intval', $cantidades), fn ($cantidad) => $cantidad > 0);

        if (empty($cantidades)) {
            throw ValidationException::withMessages(['items' => 'Selecciona al menos un producto.']);
        }

        $productos = $this->productosPedidos($cantidades);
        $this->verificarDisponibilidad($productos->map(fn ($producto) => [$producto, $cantidades[$producto->id]]));

        return DB::transaction(function () use ($datos, $cantidades, $origen, $mozo, $productos) {
            $total = $productos->sum(fn ($producto) => $producto->precio_venta * $cantidades[$producto->id]);

            $pedido = Pedido::create([
                'dni' => $datos['dni'],
                'cliente_nombre' => $datos['cliente_nombre'] ?? null,
                'mesa' => $datos['mesa'] ?? null,
                'observacion' => $datos['observacion'] ?? null,
                'origen' => $origen,
                'mozo_id' => $mozo?->id,
                'estado' => Pedido::PENDIENTE,
                'total' => round($total, 2),
            ]);

            foreach ($productos as $producto) {
                $pedido->items()->create([
                    'producto_id' => $producto->id,
                    'producto_nombre' => $producto->nombre,
                    'precio_unitario' => $producto->precio_venta,
                    'cantidad' => $cantidades[$producto->id],
                    'subtotal' => round($producto->precio_venta * $cantidades[$producto->id], 2),
                ]);
            }

            return $pedido;
        });
    }

    // ==========================================
    // AGREGAR ADICIONALES A UN PEDIDO PENDIENTE (misma mesa, mismo cobro)
    // Si el producto ya está en el pedido con el mismo precio, se suma a esa línea;
    // lo ya enviado a cocina no cambia, así el próximo ticket lleva solo lo nuevo.
    // ==========================================
    public function agregarItems(int $pedidoId, array $cantidades): Pedido
    {
        $cantidades = array_filter(array_map('intval', $cantidades), fn ($cantidad) => $cantidad > 0);

        if (empty($cantidades)) {
            throw ValidationException::withMessages(['items' => 'Selecciona al menos un producto para agregar.']);
        }

        return DB::transaction(function () use ($pedidoId, $cantidades) {
            $pedido = Pedido::lockForUpdate()->findOrFail($pedidoId);

            if ($pedido->estado !== Pedido::PENDIENTE) {
                throw ValidationException::withMessages(['pedido' => 'Solo se pueden agregar productos a un pedido pendiente.']);
            }

            $productos = $this->productosPedidos($cantidades);
            $items = $pedido->items()->with('producto.insumos')->get();

            // Lo que ya tiene el pedido más lo nuevo debe alcanzar con el stock actual
            $lineas = $items->filter(fn ($item) => $item->producto)
                ->map(fn ($item) => [$item->producto, $item->cantidad])
                ->concat($productos->map(fn ($producto) => [$producto, $cantidades[$producto->id]]));
            $this->verificarDisponibilidad($lineas);

            foreach ($productos as $producto) {
                $cantidad = $cantidades[$producto->id];
                $existente = $items->first(fn ($item) => $item->producto_id === $producto->id
                    && abs((float) $item->precio_unitario - (float) $producto->precio_venta) < 0.005);

                if ($existente) {
                    $existente->update([
                        'cantidad' => $existente->cantidad + $cantidad,
                        'subtotal' => round($existente->precio_unitario * ($existente->cantidad + $cantidad), 2),
                    ]);
                } else {
                    $pedido->items()->create([
                        'producto_id' => $producto->id,
                        'producto_nombre' => $producto->nombre,
                        'precio_unitario' => $producto->precio_venta,
                        'cantidad' => $cantidad,
                        'subtotal' => round($producto->precio_venta * $cantidad, 2),
                    ]);
                }
            }

            $pedido->update(['total' => round((float) $pedido->items()->sum('subtotal'), 2)]);

            return $pedido;
        });
    }

    // ==========================================
    // QUITAR DEL PEDIDO PENDIENTE: resta unidades de una línea o la elimina.
    // No toca stock (todavía no se descontó). Devuelve [pedido, nombre, quitadas, ya_en_cocina].
    // ==========================================
    public function quitarItem(int $pedidoId, int $itemId, ?int $cantidad = null): array
    {
        return DB::transaction(function () use ($pedidoId, $itemId, $cantidad) {
            $pedido = Pedido::lockForUpdate()->findOrFail($pedidoId);

            if ($pedido->estado !== Pedido::PENDIENTE) {
                throw ValidationException::withMessages(['pedido' => 'Solo se pueden quitar productos de un pedido pendiente.']);
            }

            $item = $pedido->items()->whereKey($itemId)->firstOrFail();
            $quitar = $cantidad === null ? (int) $item->cantidad : min(max(1, $cantidad), (int) $item->cantidad);
            $queda = $item->cantidad - $quitar;

            if ($queda === 0 && $pedido->items()->count() === 1) {
                throw ValidationException::withMessages(['pedido' => 'Es el único producto del pedido: si ya no lo quiere, anula el pedido.']);
            }

            // Si ya había salido a cocina, se avisa para que cocina no lo prepare
            $enCocina = min($quitar, max(0, (int) $item->cantidad_enviada - $queda));

            if ($queda === 0) {
                $item->delete();
            } else {
                $item->update([
                    'cantidad' => $queda,
                    'cantidad_enviada' => min((int) $item->cantidad_enviada, $queda),
                    'subtotal' => round($item->precio_unitario * $queda, 2),
                ]);
            }

            $pedido->update(['total' => round((float) $pedido->items()->sum('subtotal'), 2)]);

            return [$pedido, $item->producto_nombre, $quitar, $enCocina];
        });
    }

    // ==========================================
    // COBRAR PEDIDO: aquí se descuenta el stock
    // $pagos = [['metodo' => , 'monto' => , 'numero_operacion' => ], ...]
    // ==========================================
    public function cobrar(int $pedidoId, array $pagos, User $cajero): Recibo
    {
        return DB::transaction(function () use ($pedidoId, $pagos, $cajero) {
            $pedido = Pedido::lockForUpdate()->findOrFail($pedidoId);

            if ($pedido->estado !== Pedido::PENDIENTE) {
                throw ValidationException::withMessages(['pedido' => 'Este pedido ya fue pagado o anulado.']);
            }

            $pagado = array_sum(array_map(fn ($pago) => round((float) $pago['monto'], 2), $pagos));

            if (abs($pagado - (float) $pedido->total) >= 0.005) {
                throw ValidationException::withMessages([
                    'pagos' => 'La suma de los pagos (S/ '.number_format($pagado, 2).') debe ser igual al total (S/ '.number_format((float) $pedido->total, 2).').',
                ]);
            }

            $this->descontarStock($pedido);

            $recibo = Recibo::create([
                'pedido_id' => $pedido->id,
                'cajero_id' => $cajero->id,
                'total' => $pedido->total,
            ]);

            foreach ($pagos as $pago) {
                $recibo->pagos()->create([
                    'metodo' => $pago['metodo'],
                    'monto' => round((float) $pago['monto'], 2),
                    'numero_operacion' => $pago['numero_operacion'] ?? null,
                ]);
            }

            $pedido->update(['estado' => Pedido::PAGADO]);

            return $recibo;
        });
    }

    // ==========================================
    // ANULAR PEDIDO PENDIENTE
    // ==========================================
    public function anular(int $pedidoId): void
    {
        DB::transaction(function () use ($pedidoId) {
            $pedido = Pedido::lockForUpdate()->findOrFail($pedidoId);

            if ($pedido->estado !== Pedido::PENDIENTE) {
                throw ValidationException::withMessages(['pedido' => 'Solo se pueden anular pedidos pendientes.']);
            }

            $pedido->update(['estado' => Pedido::ANULADO]);
        });
    }

    // Productos que se pueden pedir (con precio); falla si alguno ya no existe
    private function productosPedidos(array $cantidades)
    {
        $productos = Producto::with('insumos')
            ->whereKey(array_keys($cantidades))
            ->whereNotNull('precio_venta')
            ->get();

        if ($productos->count() !== count($cantidades)) {
            throw ValidationException::withMessages(['items' => 'Uno de los productos ya no está disponible.']);
        }

        return $productos;
    }

    // Revisa sin descontar que el stock alcance. $lineas = colección de [Producto, cantidad]
    private function verificarDisponibilidad($lineas): void
    {
        [$necesarioProductos, $necesarioInsumos] = $this->calcularNecesario($lineas, 'items');

        foreach (Producto::whereKey(array_keys($necesarioProductos))->get() as $producto) {
            if ($producto->stock < $necesarioProductos[$producto->id]) {
                throw ValidationException::withMessages(['items' => "Stock insuficiente de {$producto->nombre}."]);
            }
        }

        foreach (Insumo::whereKey(array_keys($necesarioInsumos))->get() as $insumo) {
            if (round((float) $insumo->stock - $necesarioInsumos[$insumo->id], 3) < 0) {
                throw ValidationException::withMessages(['items' => "No alcanza el insumo {$insumo->nombre} para este pedido."]);
            }
        }
    }

    private function descontarStock(Pedido $pedido): void
    {
        $lineas = $pedido->items()->with('producto.insumos')->get()
            ->map(fn ($item) => [$item->producto, $item->cantidad]);

        [$necesarioProductos, $necesarioInsumos] = $this->calcularNecesario($lineas, 'pedido');

        // Se bloquean en orden de id para evitar interbloqueos entre dos cobros simultáneos.
        ksort($necesarioProductos);
        ksort($necesarioInsumos);

        foreach ($necesarioProductos as $id => $necesario) {
            $producto = Producto::lockForUpdate()->findOrFail($id);

            if ($producto->stock < $necesario) {
                throw ValidationException::withMessages([
                    'pedido' => "Stock insuficiente de {$producto->nombre}: quedan {$producto->stock} y se necesitan {$necesario}.",
                ]);
            }

            $producto->decrement('stock', $necesario);
        }

        foreach ($necesarioInsumos as $id => $necesario) {
            $insumo = Insumo::lockForUpdate()->findOrFail($id);

            if (round((float) $insumo->stock - $necesario, 3) < 0) {
                throw ValidationException::withMessages([
                    'pedido' => "No alcanza el insumo {$insumo->nombre}: quedan ".Insumo::formatear($insumo->stock)
                        ." {$insumo->unidad} y se necesitan ".Insumo::formatear($necesario).'.',
                ]);
            }

            $insumo->decrement('stock', $necesario);
        }
    }

    // Suma por insumo porque dos platos del mismo pedido pueden usar el mismo insumo.
    // $lineas = colección de [Producto, cantidad]
    private function calcularNecesario($lineas, string $campoError): array
    {
        $productos = [];
        $insumos = [];

        foreach ($lineas as [$producto, $cantidad]) {
            if (! $producto->esPlato()) {
                $productos[$producto->id] = ($productos[$producto->id] ?? 0) + $cantidad;

                continue;
            }

            if ($producto->insumos->isEmpty()) {
                throw ValidationException::withMessages([$campoError => "{$producto->nombre} no tiene receta registrada."]);
            }

            foreach ($producto->insumos as $insumo) {
                $insumos[$insumo->id] = ($insumos[$insumo->id] ?? 0) + $insumo->pivot->cantidad * $cantidad;
            }
        }

        return [$productos, $insumos];
    }
}
