{{-- ==========================================
     ACTUALIZACIÓN AUTOMÁTICA: cada pocos segundos vuelve a pedir esta misma página
     y reemplaza solo los bloques marcados con data-auto="nombre" (no toca buscadores ni formularios).
     Con $avisar = true, los ids nuevos de [data-aviso-id] suenan, salen en un aviso ("pedido nuevo" o, con
     data-aviso-tipo="adicional", "adicional por imprimir") y en el título de la pestaña, y las filas
     [data-pedido-id] del pedido indicado en data-aviso-pedido se resaltan un minuto.
     Uso: @include('partials.auto-actualizar', ['avisar' => true, 'intervalo' => 5])
========================================== --}}
<div id="avisoNuevos" class="hidden fixed bottom-4 right-4 z-50 bg-gray-900 text-white rounded-lg shadow-lg px-4 py-3 text-sm font-semibold" role="status" aria-live="polite"></div>

<style>
    tr.pedido-nuevo, tr.pedido-nuevo:hover { background: #fef3c7; }
    tr.pedido-nuevo td:first-child { box-shadow: inset 4px 0 0 #f59e0b; }
</style>

<script>
    (function () {
        const intervalo = {{ (int) ($intervalo ?? 5) }} * 1000;
        const avisar = @json((bool) ($avisar ?? false));
        const tituloBase = document.title;
        const resaltados = new Map();
        let sinLeer = 0;
        let audio = null;
        let temporizador = null;

        // Cada aviso: id único, tipo (pedido | adicional) y el pedido al que pertenece (para resaltar su fila)
        const avisosActuales = () => new Map([...document.querySelectorAll('[data-aviso-id]')].map(e => [e.dataset.avisoId, e]));
        let conocidos = new Set(avisosActuales().keys());

        // El navegador solo deja sonar después de un clic en la página
        document.addEventListener('click', function () {
            try { audio = audio || new (window.AudioContext || window.webkitAudioContext)(); audio.resume(); } catch (e) {}
        });

        function sonar() {
            if (!audio) return;
            [0, 0.18].forEach(function (retraso, i) {
                const osc = audio.createOscillator();
                const vol = audio.createGain();
                osc.frequency.value = i ? 1175 : 880;
                vol.gain.setValueAtTime(0.0001, audio.currentTime + retraso);
                vol.gain.exponentialRampToValueAtTime(0.25, audio.currentTime + retraso + 0.02);
                vol.gain.exponentialRampToValueAtTime(0.0001, audio.currentTime + retraso + 0.16);
                osc.connect(vol).connect(audio.destination);
                osc.start(audio.currentTime + retraso);
                osc.stop(audio.currentTime + retraso + 0.18);
            });
        }

        function mostrarAviso(pedidos, adicionales) {
            const aviso = document.getElementById('avisoNuevos');
            const partes = [];
            if (pedidos) partes.push(pedidos === 1 ? '1 pedido nuevo' : pedidos + ' pedidos nuevos');
            if (adicionales) partes.push(adicionales === 1 ? '1 adicional por imprimir' : adicionales + ' adicionales por imprimir');
            aviso.innerHTML = '<i class="fas fa-bell text-amber-400 mr-2"></i>' + partes.join(' · ');
            aviso.classList.remove('hidden');
            clearTimeout(aviso._ocultar);
            aviso._ocultar = setTimeout(() => aviso.classList.add('hidden'), 8000);
        }

        function marcarResaltados() {
            const ahora = Date.now();
            resaltados.forEach(function (desde, id) {
                const filas = document.querySelectorAll('[data-pedido-id="' + id + '"]');
                if (ahora - desde > 60000) {
                    resaltados.delete(id);
                    filas.forEach(f => f.classList.remove('pedido-nuevo'));
                } else {
                    filas.forEach(f => f.classList.add('pedido-nuevo'));
                }
            });
        }

        async function actualizar() {
            try {
                const respuesta = await fetch(location.href, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    cache: 'no-store',
                    credentials: 'same-origin',
                });
                // Sesión vencida: deja de consultar (al recargar irá al login)
                if (!respuesta.ok || new URL(respuesta.url).pathname !== location.pathname) {
                    clearInterval(temporizador);
                    return;
                }
                const pagina = new DOMParser().parseFromString(await respuesta.text(), 'text/html');
                pagina.querySelectorAll('[data-auto]').forEach(function (nuevo) {
                    const actual = document.querySelector('[data-auto="' + nuevo.dataset.auto + '"]');
                    if (actual && actual.innerHTML !== nuevo.innerHTML) actual.innerHTML = nuevo.innerHTML;
                });

                const ahora = avisosActuales();
                const llegaron = [...ahora.keys()].filter(id => !conocidos.has(id));
                conocidos = new Set([...conocidos, ...ahora.keys()]);
                if (avisar && llegaron.length) {
                    const adicionales = llegaron.filter(id => ahora.get(id).dataset.avisoTipo === 'adicional').length;
                    llegaron.forEach(id => resaltados.set(ahora.get(id).dataset.avisoPedido || id, Date.now()));
                    sinLeer += llegaron.length;
                    document.title = '(' + sinLeer + ') ' + tituloBase;
                    mostrarAviso(llegaron.length - adicionales, adicionales);
                    sonar();
                }
                marcarResaltados();
            } catch (e) {
                // Sin conexión: se vuelve a intentar en la próxima vuelta
            }
        }

        window.addEventListener('focus', function () { sinLeer = 0; document.title = tituloBase; });
        temporizador = setInterval(actualizar, intervalo);
    })();
</script>
