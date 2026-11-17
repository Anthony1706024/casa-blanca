<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo {{ $recibo->numero }} | Casa Blanca</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body { background: #f3f4f6; font-family: 'Segoe UI', Arial, sans-serif; color: #1a1a1a; }
        @media print {
            body { background: #fff; }
            .no-imprimir { display: none !important; }
            .ticket { box-shadow: none !important; border: 0 !important; margin: 0 !important; max-width: 100% !important; }
        }
    </style>
</head>
<body class="p-4">
    @php($volver = auth()->user()->isAdmin() ? route('admin.ventas.index') : route('cajera.pedidos.index'))

    <div class="no-imprimir max-w-md mx-auto flex justify-between items-center mb-4">
        <a href="{{ $volver }}" class="text-gray-600 hover:text-black text-sm"><i class="fas fa-arrow-left mr-1"></i> Volver</a>
        <button onclick="window.print()" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-semibold text-sm">
            <i class="fas fa-print mr-1"></i> Imprimir
        </button>
    </div>

    @if(session('success'))
        <div class="no-imprimir max-w-md mx-auto bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">
            <i class="fas fa-check-circle text-green-500 mr-2"></i>{{ session('success') }}
        </div>
    @endif

    <div class="ticket max-w-md mx-auto bg-white rounded-lg border border-gray-200 shadow-sm p-6 text-sm">
        <div class="text-center border-b border-dashed border-gray-300 pb-4 mb-4">
            <h1 class="text-xl font-bold">CASA BLANCA</h1>
            <p class="text-gray-500">Recibo de pago</p>
            <p class="font-mono font-semibold text-orange-600 mt-1">{{ $recibo->numero }}</p>
        </div>

        <dl class="grid grid-cols-2 gap-y-1 border-b border-dashed border-gray-300 pb-4 mb-4">
            <dt class="text-gray-500">Fecha</dt>
            <dd class="text-right">{{ $recibo->created_at->format('d/m/Y H:i') }}</dd>
            <dt class="text-gray-500">Pedido</dt>
            <dd class="text-right font-mono">{{ $recibo->pedido->numero }}</dd>
            <dt class="text-gray-500">DNI</dt>
            <dd class="text-right font-semibold">{{ $recibo->pedido->dni }}</dd>
            @if($recibo->pedido->cliente_nombre)
                <dt class="text-gray-500">Cliente</dt>
                <dd class="text-right">{{ $recibo->pedido->cliente_nombre }}</dd>
            @endif
            @if($recibo->pedido->mesa)
                <dt class="text-gray-500">Mesa</dt>
                <dd class="text-right">{{ $recibo->pedido->mesa }}</dd>
            @endif
            @if($recibo->cajero)
                <dt class="text-gray-500">Atendió</dt>
                <dd class="text-right">{{ $recibo->cajero->name }}</dd>
            @endif
        </dl>

        <table class="w-full mb-4">
            <thead>
                <tr class="text-gray-500 text-left">
                    <th class="font-medium pb-1">Cant.</th>
                    <th class="font-medium pb-1">Producto</th>
                    <th class="font-medium pb-1 text-right">Importe</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recibo->pedido->items as $item)
                    <tr>
                        <td class="py-0.5 align-top">{{ $item->cantidad }}</td>
                        <td class="py-0.5">{{ $item->producto_nombre }}<br><span class="text-gray-400 text-xs">S/ {{ number_format($item->precio_unitario, 2) }} c/u</span></td>
                        <td class="py-0.5 align-top text-right">S/ {{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="flex justify-between text-lg font-bold border-t border-dashed border-gray-300 pt-3 mb-4">
            <span>TOTAL</span>
            <span>S/ {{ number_format($recibo->total, 2) }}</span>
        </div>

        <h2 class="font-semibold mb-2">Forma de pago</h2>
        @foreach($recibo->pagos as $pago)
            <div class="flex justify-between gap-4 py-1">
                <div>
                    <div>{{ $pago->metodo_nombre }}</div>
                    <div class="text-gray-500 text-xs">N.° de operación: {{ $pago->numero_operacion ?: '—' }}</div>
                </div>
                <div class="font-semibold">S/ {{ number_format($pago->monto, 2) }}</div>
            </div>
        @endforeach

        <p class="text-center text-gray-400 text-xs mt-6">¡Gracias por su visita!</p>
    </div>
</body>
</html>
