<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets de cocina {{ $pedido->numero }} | Casa Blanca</title>

    {{-- Sin estilos externos: debe imprimirse igual sin internet. Cada ticket va en su propia hoja para que la ticketera corte entre uno y otro. --}}
    <style>
        @page { size: {{ $ancho }}mm auto; margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #e5e7eb; font-family: Arial, Helvetica, sans-serif; color: #000; }

        .acciones { max-width: 420px; margin: 16px auto; display: flex; gap: 8px; align-items: center; justify-content: space-between; font-size: 14px; }
        .acciones a, .acciones button { font: inherit; border: 0; border-radius: 8px; padding: 9px 14px; cursor: pointer; text-decoration: none; }
        .volver { color: #374151; background: transparent; }
        .imprimir { background: #111827; color: #fff; font-weight: 600; }
        .aviso { max-width: 420px; margin: 0 auto 12px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; border-radius: 8px; padding: 10px 14px; font-size: 14px; }

        .ticket { width: {{ $ancho === '58' ? '48mm' : '72mm' }}; margin: 0 auto 24px; background: #fff; padding: 5mm {{ $ancho === '58' ? '2mm' : '3mm' }}; font-size: {{ $ancho === '58' ? '12px' : '13.5px' }}; line-height: 1.35; box-shadow: 0 2px 10px rgba(0, 0, 0, .15); page-break-after: always; break-after: page; }
        .ticket:last-of-type { page-break-after: auto; break-after: auto; }
        .centro { text-align: center; }
        .cocina { font-size: 1.9em; font-weight: 900; letter-spacing: .04em; }
        .marca { font-weight: 800; margin-top: 2px; }
        .mesa { font-size: 1.7em; font-weight: 900; margin: 4px 0; }
        .nota { font-size: .85em; }
        .linea { border-top: 1px dashed #000; margin: 7px 0; }
        .doble { border-top: 2px solid #000; margin: 7px 0; }
        .fila { display: flex; justify-content: space-between; gap: 8px; }
        .item { display: flex; gap: 8px; font-size: 1.35em; font-weight: 800; line-height: 1.3; margin: 3px 0; }
        .item .cant { min-width: 2.4em; }
        .obs { font-weight: 700; }

        @media print {
            body { background: #fff; }
            .acciones, .aviso { display: none !important; }
            .ticket { margin: 0; box-shadow: none; }
        }
    </style>
</head>
<body>
    @php
        $total = count($lote['tickets']);
        $numero = 0;
        $ahora = now();
        $hora = $ahora->format('h:i').' '.($ahora->format('A') === 'AM' ? 'a. m.' : 'p. m.');
    @endphp

    <div class="acciones">
        <a href="{{ route('cajera.pedidos.show', $pedido->id) }}" class="volver">← Volver al pedido</a>
        <button type="button" class="imprimir" onclick="window.print()">Imprimir {{ $total }} ticket{{ $total === 1 ? '' : 's' }}</button>
    </div>
    @if($recienEnviado)
        <div class="aviso">Se marcaron como enviados a cocina. Si no salió bien, imprime de nuevo con el botón.</div>
    @endif

    @foreach($lote['tickets'] as $cocina => $lineas)
        @php $numero++; @endphp
        <section class="ticket">
            <div class="centro">
                <div class="cocina">{{ mb_strtoupper(\App\Models\Producto::COCINAS[$cocina] ?? $cocina) }}</div>
                @if($lote['adicional'])<div class="marca">*** ADICIONAL ***</div>@endif
                @if($lote['reimpresion'])<div class="marca">(REIMPRESIÓN)</div>@endif
                <div class="nota">Ticket {{ $numero }} de {{ $total }}</div>
            </div>
            <div class="doble"></div>
            <div class="centro mesa">{{ $pedido->mesa ? 'MESA '.$pedido->mesa : 'SIN MESA' }}</div>
            <div class="fila"><span>Pedido: {{ $pedido->numero }}</span><span>{{ $ahora->format('d/m') }} {{ $hora }}</span></div>
            @if($pedido->cliente_nombre)<div>Cliente: {{ $pedido->cliente_nombre }}</div>@endif
            <div>Atiende: {{ $pedido->mozo ? \Illuminate\Support\Str::before($pedido->mozo->name.' ', ' ') : 'Pedido del cliente' }}</div>
            <div class="linea"></div>
            @foreach($lineas as $linea)
                <div class="item"><span class="cant">{{ $linea['cantidad'] }} x</span><span>{{ $linea['nombre'] }}</span></div>
            @endforeach
            <div class="linea"></div>
            @if($pedido->observacion)
                <div class="obs">OBSERVACIÓN:</div>
                <div>{{ $pedido->observacion }}</div>
                <div class="linea"></div>
            @endif
        </section>
    @endforeach

    @if($recienEnviado)
        <script>window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 300); });</script>
    @endif
</body>
</html>
