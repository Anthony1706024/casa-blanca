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

        $productos = Producto::with('insumos')
            ->whereKey(array_keys($cantidades))
            ->whereNotNull('precio_venta')
            ->get();

        if ($productos->count() !== count($cantidades)) {
            throw ValidationException::withMessages(['items' => 'Uno de los productos ya no está disponible.']);
        }

        $lineas = $productos->map(fn ($producto) => [$producto, $cantidades[$producto->id]]);
        [$necesarioProductos, $necesarioInsumos] = $this->calcularNecesario($lineas, 'items');

        foreach ($productos->whereIn('id', array_keys($necesarioProductos)) as $producto) {
            if ($producto->stock < $necesarioProductos[$producto->id]) {
                throw ValidationException::withMessages(['items' => "Stock insuficiente de {$producto->nombre}."]);
            }
        }

        foreach (Insumo::whereKey(array_keys($necesarioInsumos))->get() as $insumo) {
            if (round((float) $insumo->stock - $necesarioInsumos[$insumo->id], 3) < 0) {
                throw ValidationException::withMessages(['items' => "No alcanza el insumo {$insumo->nombre} para este pedido."]);
            }
        }

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
