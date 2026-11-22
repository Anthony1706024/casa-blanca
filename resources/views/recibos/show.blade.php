<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $recibo ? 'Voucher '.str_pad($recibo->id, 7, '0', STR_PAD_LEFT) : 'Cuenta '.$pedido->numero }} | Casa Blanca</title>

    {{-- Sin estilos externos: el voucher debe imprimirse igual sin internet --}}
    <style>
        @page { size: {{ $ancho }}mm auto; margin: 0; }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #e5e7eb; font-family: Arial, Helvetica, sans-serif; color: #000; }

        /* Barra de acciones, solo en pantalla */
        .acciones { max-width: 420px; margin: 16px auto; display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: space-between; font-size: 14px; }
        .acciones a, .acciones button { font: inherit; border: 0; border-radius: 8px; padding: 9px 14px; cursor: pointer; text-decoration: none; }
        .volver { color: #374151; background: transparent; }
        .imprimir { background: #f97316; color: #fff; font-weight: 600; }
        .imprimir:hover { background: #ea580c; }
        .papel { color: #374151; background: #fff; }
        .papel.activo { background: #1f2937; color: #fff; }
        .aviso { max-width: 420px; margin: 0 auto 12px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; border-radius: 8px; padding: 10px 14px; font-size: 14px; }

        /* El ticket: 80 mm de papel imprimen ~72 mm; 58 mm imprimen ~48 mm */
        .ticket { width: {{ $ancho === '58' ? '48mm' : '72mm' }}; margin: 0 auto 24px; background: #fff; padding: 5mm {{ $ancho === '58' ? '2mm' : '3mm' }}; font-size: {{ $ancho === '58' ? '11px' : '12.5px' }}; line-height: 1.35; box-shadow: 0 2px 10px rgba(0, 0, 0, .15); }
        /* Letra de ticketera (monoespaciada), como sale impreso. El encabezado conserva sus letras de adorno. */
        /* Lucida Console: ceros lisos (sin punto ni raya), como la letra de la ticketera */
        .ticket { font-family: 'Lucida Console', 'Lucida Sans Typewriter', 'Courier New', monospace; letter-spacing: -.03em; font-size: {{ $ancho === '58' ? '9.5px' : '11px' }}; }
        .ticket .fila > span { white-space: nowrap; }
        /* Nada en negrita, como en la ticketera */
        .ticket, .ticket * { font-weight: normal !important; }
        /* El encabezado conserva el tamaño con el que quedó ajustado */
        .encabezado { font-size: {{ $ancho === '58' ? '11px' : '12.5px' }}; }
        .cerrar { color: #374151; background: #fff; }

        /* Camarón a la izquierda y el nombre al lado */
        .encabezado { display: flex; align-items: center; justify-content: center; gap: 4px; margin-bottom: 4px; }
        .logo { display: block; width: 22%; flex-shrink: 0; filter: grayscale(1) contrast(1.4); }
        /* Bloque de texto: más grande y un poco más abajo */
        .encabezado .centro { padding-top: 3mm; }
        /* Letras de adorno que ya trae Windows: sin internet se imprimen igual */
        .marca-1 { font-family: 'Monotype Corsiva', 'Palace Script MT', cursive; font-size: 1.5em; line-height: 1.1; white-space: nowrap; }
        .marca-2 { font-family: 'Brush Script MT', 'Segoe Script', cursive; font-size: 2.8em; line-height: 1.05; white-space: nowrap; }

        .centro { text-align: center; }
        .nombre { font-size: 1.6em; font-weight: 800; letter-spacing: .08em; }
        .subtitulo { font-weight: 700; letter-spacing: .05em; }
        .negrita { font-weight: 700; }
        .titulo { font-size: 1.1em; font-weight: 300; margin-top: 2px; }
        .nota { font-size: .85em; }
        /* Separadores hechos con guiones e iguales, como los imprime la ticketera (no líneas rectas) */
        .linea, .doble { overflow: hidden; white-space: nowrap; line-height: 1; margin: 5px 0; }
        .linea::before { content: '----------------------------------------------------------------------------------------'; }
        .doble::before { content: '========================================================================================'; }
        .fila { display: flex; justify-content: space-between; gap: 8px; }
        @if($ancho === '58')
            /* En 58 mm no caben dos datos por fila */
            .fila { display: block; }
            .fila > span { display: block; }
            .marca-1 { font-size: 1.3em; }
            .marca-2 { font-size: 2.3em; }
        @endif

        table { width: 100%; border-collapse: collapse; }
        th { font-weight: 700; text-align: left; padding-bottom: 0; }
        /* Ancho fijo: la fila de guiones no estira la tabla */
        table { table-layout: fixed; }
        th.importe { width: 6.5em; text-align: right; }
        th.cant { width: 3.6em; }
        td { padding: 3px 0 0; vertical-align: top; }
        .cant { width: 2.6em; }
        .importe { text-align: right; white-space: nowrap; padding-left: 6px; }

        .total { font-size: 1.15em; font-weight: 400; text-align: center; }
        .total strong { display: block; font-size: 1.6em; font-weight: 400; }
        .gracias { font-weight: 800; font-size: 1em; }

        @media print {
            body { background: #fff; }
            .acciones, .aviso { display: none !important; }
            .ticket { margin: 0; box-shadow: none; }
        }
    </style>
</head>
<body>
    @php
        // Con $recibo: comprobante de un pedido cobrado. Sin $recibo: cuenta para la mesa, el pedido sigue pendiente.
        $volver = auth()->user()->isAdmin() ? route('admin.ventas.index') : route('cajera.pedidos.index');
        $pedido = $recibo ? $recibo->pedido : $pedido;
        $fecha = $recibo ? $recibo->created_at : now();
        $hora = $fecha->format('h:i').' '.($fecha->format('A') === 'AM' ? 'a. m.' : 'p. m.');
        $atendio = $pedido->mozo ?? $recibo?->cajero;
        $acabaDeCobrar = $recibo && session()->has('success');
    @endphp

    <div class="acciones">
        {{-- Abierta en ventana aparte: se cierra; si no, se vuelve a la lista --}}
        <a href="{{ $volver }}" class="volver" data-volver>← Volver</a>
        <button type="button" class="cerrar" data-cerrar hidden onclick="window.close()">✕ Cerrar ventana</button>
        <span>
            <a href="?ancho=80" class="papel {{ $ancho === '80' ? 'activo' : '' }}">80 mm</a>
            <a href="?ancho=58" class="papel {{ $ancho === '58' ? 'activo' : '' }}">58 mm</a>
        </span>
        @if($ticketera)
            <form method="POST" action="{{ $recibo ? route('recibos.imprimir', $recibo->id) : route('cajera.pedidos.cuenta.imprimir', $pedido->id) }}">
                @csrf
                <button type="submit" class="imprimir">Imprimir en ticketera</button>
            </form>
        @else
            <button type="button" class="imprimir" onclick="window.print()">Imprimir {{ $recibo ? 'voucher' : 'cuenta' }}</button>
        @endif
    </div>
    <script>
        if (window.opener) {
            document.querySelector('[data-volver]').hidden = true;
            document.querySelector('[data-cerrar]').hidden = false;
        }
    </script>

    @if($acabaDeCobrar)
        <div class="aviso">{{ session('success') }}</div>
    @endif
    @if(session('impreso'))
        <div class="aviso">{{ session('impreso') }}</div>
    @endif
    @if(session('error'))
        <div class="aviso" style="background: #fef2f2; border-color: #fecaca; color: #991b1b;">{{ session('error') }}</div>
    @endif

    <div class="ticket">
        <div class="encabezado">
            <img src="{{ asset('img/camaron.webp') }}" alt="" class="logo">
            <div class="centro">
                @if($negocio['ticket_subtitulo'])<div class="marca-1">{{ \Illuminate\Support\Str::title(mb_strtolower($negocio['ticket_subtitulo'])) }}</div>@endif
                <div class="marca-2">{{ \Illuminate\Support\Str::title(mb_strtolower($negocio['ticket_nombre'])) }}</div>
            </div>
        </div>
        <div class="centro">
            @if($negocio['ticket_lugar'])<div>{{ $negocio['ticket_lugar'] }}</div>@endif
            @if($negocio['ticket_ruc'])<div class="negrita">RUC: {{ $negocio['ticket_ruc'] }}</div>@endif
        </div>

        <div class="linea"></div>
        <div class="centro">
            @if($recibo)
                <div class="titulo">COMPROBANTE DE CONSUMO</div>
                <div class="negrita" style="margin-top: 4px;">N.º {{ str_pad($recibo->id, 7, '0', STR_PAD_LEFT) }}</div>
            @else
                <div class="titulo" style="font-size: 1.6em; font-weight: 700;">CUENTA</div>
            @endif
        </div>
        <div class="linea"></div>

        <div class="fila"><span>Fecha: {{ $fecha->format('d/m/Y') }}</span><span>Hora: {{ $hora }}</span></div>
        @if($recibo)
            <div class="fila"><span>Recibo: {{ $recibo->numero }}</span><span>Pedido: {{ $pedido->numero }}</span></div>
        @else
            <div>Pedido: {{ $pedido->numero }}</div>
        @endif
        @if($pedido->mesa)<div>Mesa: {{ str_pad($pedido->mesa, 2, '0', STR_PAD_LEFT) }}</div>@endif
        @if($pedido->cliente_nombre)<div>Cliente: {{ $pedido->cliente_nombre }}</div>@endif
        @if($pedido->dni)<div>DNI: {{ $pedido->dni }}</div>@endif
        @if($atendio)<div>Atendido por: {{ \Illuminate\Support\Str::before($atendio->name.' ', ' ') }}</div>@endif

        <div class="linea"></div>
        <table>
            <thead>
                <tr><th class="cant">Cant.</th><th>Descripción</th><th class="importe">Importe</th></tr>
                <tr><td colspan="3" style="padding: 0;"><div class="linea"></div></td></tr>
            </thead>
            <tbody>
                @foreach($pedido->items as $item)
                    <tr>
                        <td class="cant">{{ str_pad($item->cantidad, 2, '0', STR_PAD_LEFT) }}</td>
                        <td>
                            {{ $item->producto_nombre }}
                            @if($item->cantidad > 1)<div class="nota">S/ {{ number_format($item->precio_unitario, 2) }} c/u</div>@endif
                        </td>
                        <td class="importe">{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="doble"></div>
        <div class="total">TOTAL A PAGAR<strong>S/ {{ number_format($recibo ? $recibo->total : $pedido->total, 2) }}</strong></div>
        <div class="doble"></div>

        @if($recibo)
            @foreach($recibo->pagos as $pago)
                <div class="fila">
                    <span>Método de pago: <span class="negrita">{{ $pago->metodo_nombre }}</span></span>
                    @if($recibo->pagos->count() > 1)<span>S/ {{ number_format($pago->monto, 2) }}</span>@endif
                </div>
                @if($pago->numero_operacion)<div class="nota">N.º de operación: {{ $pago->numero_operacion }}</div>@endif
            @endforeach
            <div class="linea"></div>
        @endif
        <div class="centro">
            <div class="gracias">¡GRACIAS POR SU PREFERENCIA!</div>
            @if($negocio['ticket_mensaje'])<div>{{ $negocio['ticket_mensaje'] }}</div>@endif
        </div>
    </div>

    @if(! $ticketera && ($acabaDeCobrar || request('imprimir') || session('imprimir')))
        {{-- Recién cobrado: se abre la impresión sola --}}
        <script>window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 300); });</script>
    @endif
</body>
</html>