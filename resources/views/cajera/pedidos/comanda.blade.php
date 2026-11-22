<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket {{ $pedido->numero }} | Casa Blanca</title>

    {{-- Sin estilos externos: debe imprimirse igual sin internet. Cada ticket va en su propia hoja para que la ticketera corte entre uno y otro.
         Se abre en una ventana aparte desde el pedido, así la cajera no pierde lo que estaba haciendo. --}}
    <style>
        @page { size: {{ $ancho }}mm auto; margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #e5e7eb; font-family: Arial, Helvetica, sans-serif; color: #000; }

        .acciones { max-width: 420px; margin: 16px auto; padding: 0 12px; display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: space-between; font-size: 14px; }
        .acciones form { display: inline; }
        .acciones button { font: inherit; border: 0; border-radius: 8px; padding: 9px 14px; cursor: pointer; }
        .cerrar { color: #374151; background: #fff; }
        .imprimir { background: #111827; color: #fff; font-weight: 600; }
        .imprimir:disabled { opacity: .6; cursor: wait; }
        .aviso { max-width: 396px; margin: 0 auto 12px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; border-radius: 8px; padding: 10px 14px; font-size: 14px; }
        .aviso.error { background: #fef2f2; border-color: #fecaca; color: #991b1b; }

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

        /* Confirmación antes de reimprimir */
        .modal { position: fixed; inset: 0; background: rgba(0, 0, 0, .5); display: flex; align-items: center; justify-content: center; padding: 16px; z-index: 10; }
        .modal[hidden] { display: none; }
        .modal-caja { background: #fff; border-radius: 10px; padding: 20px; max-width: 340px; width: 100%; box-shadow: 0 10px 30px rgba(0, 0, 0, .3); }
        .modal-caja h2 { font-size: 17px; margin-bottom: 8px; }
        .modal-caja p { font-size: 14px; color: #4b5563; margin-bottom: 16px; line-height: 1.4; }
        .modal-botones { display: flex; justify-content: flex-end; gap: 8px; }
        .modal-botones button { font: inherit; font-size: 14px; border: 0; border-radius: 8px; padding: 9px 14px; cursor: pointer; }
        .cancelar { background: #f3f4f6; color: #1f2937; }

        @media print {
            body { background: #fff; }
            .acciones, .aviso, .modal { display: none !important; }
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
        $anulado = $pedido->estado === \App\Models\Pedido::ANULADO;
    @endphp

    <div class="acciones">
        <button type="button" class="cerrar" onclick="window.close()">✕ Cerrar ventana</button>

        @unless($anulado)
            @if($porEnviar > 0)
                {{-- Falta enviar algo a cocina --}}
                <form method="POST" action="{{ route('cajera.pedidos.comanda', $pedido->id) }}" data-una-vez>
                    @csrf
                    <button type="submit" class="imprimir">Imprimir ticket cocina ({{ $porEnviar }})</button>
                </form>
            @elseif($conTicketera)
                <form method="POST" action="{{ route('cajera.pedidos.comanda', $pedido->id) }}" id="formReimprimir">
                    @csrf
                    <input type="hidden" name="todo" value="1">
                    <button type="submit" class="imprimir">Reimprimir</button>
                </form>
            @else
                <button type="button" class="imprimir" id="botonReimprimir">{{ $recienEnviado ? 'Imprimir' : 'Reimprimir' }} {{ $total }} ticket{{ $total === 1 ? '' : 's' }}</button>
            @endif
        @endunless
    </div>

    @if(session('success'))
        <div class="aviso">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="aviso error">{{ session('error') }}</div>
    @endif
    @if($recienEnviado)
        <div class="aviso">Se marcaron como enviados a cocina. Si no salió bien, imprime de nuevo con el botón.</div>
    @endif

    @unless($anulado)
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
    @endunless

    <div class="modal" id="modalReimprimir" hidden role="dialog" aria-modal="true" aria-labelledby="modalTitulo">
        <div class="modal-caja">
            <h2 id="modalTitulo">⚠ ¿Reimprimir los tickets?</h2>
            <p>Los tickets de este pedido ya se enviaron a cocina. Si los vuelves a imprimir, la cocina podría preparar el pedido dos veces.</p>
            <div class="modal-botones">
                <button type="button" class="cancelar" data-cerrar>Cancelar</button>
                <button type="button" class="imprimir" id="confirmarReimprimir">Sí, reimprimir</button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const modal = document.getElementById('modalReimprimir');
            const form = document.getElementById('formReimprimir');
            const botonNavegador = document.getElementById('botonReimprimir');
            let accion = null;
            let enviando = false;

            const cerrar = () => { modal.hidden = true; };
            const preguntar = (hacer) => { accion = hacer; modal.hidden = false; document.getElementById('confirmarReimprimir').focus(); };

            // Envío a cocina: un solo clic, sin duplicados
            document.querySelectorAll('[data-una-vez]').forEach(function (f) {
                f.addEventListener('submit', function (e) {
                    if (enviando) { e.preventDefault(); return; }
                    enviando = true;
                    f.querySelector('button').disabled = true;
                });
            });

            if (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    if (enviando) return;
                    preguntar(function () {
                        enviando = true;
                        form.querySelector('button').disabled = true;
                        form.submit();
                    });
                });
            }

            if (botonNavegador) {
                botonNavegador.addEventListener('click', function () { preguntar(() => window.print()); });
            }

            document.getElementById('confirmarReimprimir').addEventListener('click', function () { cerrar(); if (accion) accion(); });
            modal.querySelector('[data-cerrar]').addEventListener('click', cerrar);
            modal.addEventListener('click', e => { if (e.target === modal) cerrar(); });
            document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrar(); });

            @if($recienEnviado)
                window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 300); });
            @endif
        })();
    </script>
</body>
</html>
