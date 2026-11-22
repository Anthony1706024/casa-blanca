@extends('layouts.panel', ['nav' => 'cajera.nav'])

@section('titulo', 'Cobrar Pedido')
@section('icono', 'fa-money-bill-wave')
@section('subtitulo', "Pedido {$pedido->numero} · DNI {$pedido->dni}")
@section('ancho', 'max-w-5xl')

@section('acciones')
    <a href="{{ route('cajera.pedidos.index') }}" class="text-gray-600 hover:text-black transition text-sm py-2">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
@endsection

@section('contenido')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Detalle -->
        <div class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-bold text-black">Detalle</h2>
                @include('pedidos._estado', ['estado' => $pedido->estado])
            </div>

            <dl class="grid grid-cols-2 gap-y-1 text-sm mb-4">
                <dt class="text-gray-500">DNI</dt>
                <dd class="text-right font-semibold">{{ $pedido->dni }}</dd>
                @if($pedido->cliente_nombre)
                    <dt class="text-gray-500">Cliente</dt>
                    <dd class="text-right">{{ $pedido->cliente_nombre }}</dd>
                @endif
                @if($pedido->mesa)
                    <dt class="text-gray-500">Mesa</dt>
                    <dd class="text-right">{{ $pedido->mesa }}</dd>
                @endif
                <dt class="text-gray-500">Registrado por</dt>
                <dd class="text-right">{{ $pedido->mozo->name ?? 'Cliente (prepedido)' }}</dd>
                @if($pedido->observacion)
                    <dt class="text-gray-500">Observación</dt>
                    <dd class="text-right">{{ $pedido->observacion }}</dd>
                @endif
            </dl>

            <table class="w-full text-sm border-t border-gray-200">
                @foreach($pedido->items as $item)
                    <tr class="border-b border-gray-100">
                        <td class="py-2 pr-2 text-gray-600 align-top">{{ $item->cantidad }} ×</td>
                        <td class="py-2 text-black">
                            {{ $item->producto_nombre }}
                            <div class="text-xs text-gray-400">
                                {{ $item->producto->cocina_nombre ?? '' }}
                                @if($item->por_enviar > 0)
                                    · <span class="text-amber-600 font-semibold">{{ $item->por_enviar === (int) $item->cantidad ? 'Por enviar a cocina' : $item->por_enviar.' por enviar a cocina' }}</span>
                                @else
                                    · <span class="text-green-600"><i class="fas fa-check"></i> En cocina</span>
                                @endif
                            </div>
                        </td>
                        <td class="py-2 text-right whitespace-nowrap align-top">S/ {{ number_format($item->subtotal, 2) }}</td>
                        @if($pedido->estado === \App\Models\Pedido::PENDIENTE)
                            @php $avisoCocina = $item->cantidad_enviada > 0 ? ' Ya salió a cocina: avisa que no lo preparen.' : ''; @endphp
                            <td class="py-2 pl-2 align-top whitespace-nowrap text-right">
                                @if($item->cantidad > 1)
                                    <form method="POST" action="{{ route('cajera.pedidos.items.quitar', [$pedido->id, $item->id]) }}" class="inline"
                                          onsubmit="return confirm({{ \Illuminate\Support\Js::from('¿Quitar 1 × '.$item->producto_nombre.'?'.$avisoCocina) }})">
                                        @csrf
                                        <input type="hidden" name="cantidad" value="1">
                                        <button type="submit" title="Quitar uno" aria-label="Quitar uno de {{ $item->producto_nombre }}" class="text-gray-600 bg-gray-100 hover:bg-gray-200 w-7 h-7 rounded font-bold">−</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('cajera.pedidos.items.quitar', [$pedido->id, $item->id]) }}" class="inline"
                                      onsubmit="return confirm({{ \Illuminate\Support\Js::from('¿Quitar '.$item->cantidad.' × '.$item->producto_nombre.' del pedido?'.$avisoCocina) }})">
                                    @csrf
                                    <button type="submit" title="Quitar del pedido" aria-label="Quitar {{ $item->producto_nombre }} del pedido" class="text-red-600 bg-red-50 hover:bg-red-100 w-7 h-7 rounded"><i class="fas fa-trash-alt text-xs"></i></button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </table>

            <div class="flex justify-between text-xl font-bold mt-4">
                <span>Total</span>
                <span class="text-orange-600">S/ {{ number_format($pedido->total, 2) }}</span>
            </div>

            @if($pedido->estado === \App\Models\Pedido::PENDIENTE)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-5 pt-5 border-t border-gray-200">
                    <a href="{{ route('cajera.pedidos.adicionales', $pedido->id) }}" class="text-center border-2 border-orange-500 text-orange-600 hover:bg-orange-50 px-4 py-2.5 rounded-lg font-semibold transition text-sm">
                        <i class="fas fa-plus-circle mr-1"></i> Agregar adicionales
                    </a>
                    {{-- El ticket se abre en otra ventana: esta pestaña no se recarga --}}
                    <form method="POST" action="{{ route('cajera.pedidos.comanda', $pedido->id) }}" id="formTicket" data-modo="{{ $porEnviar > 0 ? 'nuevo' : 'reimprimir' }}">
                        @csrf
                        <input type="hidden" name="todo" value="1" @if($porEnviar > 0) disabled @endif>
                        <button type="submit" data-boton="nuevo" class="{{ $porEnviar > 0 ? '' : 'hidden' }} w-full bg-gray-900 hover:bg-black text-white px-4 py-2.5 rounded-lg font-semibold transition text-sm">
                            <i class="fas fa-print mr-1"></i> Imprimir ticket cocina
                            <span class="ml-1 bg-amber-400 text-black text-xs font-bold rounded-full px-2 py-0.5">{{ $porEnviar }}</span>
                        </button>
                        <button type="submit" data-boton="reimprimir" class="{{ $porEnviar > 0 ? 'hidden' : '' }} w-full bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2.5 rounded-lg font-semibold transition text-sm">
                            <i class="fas fa-redo mr-1"></i> Ticket
                        </button>
                    </form>
                </div>
                <p class="text-xs text-gray-400 mt-2">
                    Sale un ticket por cocina, en este orden: {{ implode(', ', \App\Models\Producto::COCINAS) }}. Solo se imprime lo que aún no se envió.
                </p>
            @endif
        </div>

        @if($pedido->estado === \App\Models\Pedido::PENDIENTE)
            <!-- Confirmación antes de reimprimir -->
            <div id="modalReimprimir" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="modalReimprimirTitulo">
                <div class="bg-white rounded-lg shadow-xl max-w-sm w-full p-6">
                    <h3 id="modalReimprimirTitulo" class="font-bold text-black text-lg mb-2">
                        <i class="fas fa-exclamation-triangle text-amber-500 mr-1"></i> ¿Reimprimir los tickets?
                    </h3>
                    <p class="text-sm text-gray-600 mb-5">
                        Los tickets de este pedido ya se enviaron a cocina.
                        Si los vuelves a imprimir, la cocina podría preparar el pedido dos veces.
                    </p>
                    <div class="flex justify-end gap-3">
                        <button type="button" data-cerrar class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-semibold text-sm">Cancelar</button>
                        <button type="button" id="confirmarReimprimir" class="bg-gray-900 hover:bg-black text-white px-4 py-2 rounded-lg font-semibold text-sm">
                            <i class="fas fa-redo mr-1"></i> Sí, reimprimir
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Cobro -->
        <div class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm">
            @if($pedido->estado === \App\Models\Pedido::PENDIENTE)
                <h2 class="font-bold text-black mb-4">Registrar pago</h2>

                <form method="POST" action="{{ route('cajera.pedidos.cobrar', $pedido->id) }}" id="formCobro">
                    @csrf

                    @foreach([0, 1] as $i)
                        <fieldset id="pago{{ $i }}" data-pago class="border border-gray-200 rounded-lg p-4 mb-4 {{ $i === 1 && ! old('pagos.1') ? 'hidden' : '' }}" {{ $i === 1 && ! old('pagos.1') ? 'disabled' : '' }}>
                            <legend class="px-2 text-sm font-semibold text-gray-700">Pago {{ $i + 1 }}</legend>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="etiqueta" for="metodo{{ $i }}">Transacción</label>
                                    <select id="metodo{{ $i }}" name="pagos[{{ $i }}][metodo]" data-metodo class="campo" required>
                                        @foreach($metodos as $valor => $texto)
                                            <option value="{{ $valor }}" {{ old("pagos.$i.metodo") === $valor ? 'selected' : '' }}>{{ $texto }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="etiqueta" for="monto{{ $i }}">Monto (S/)</label>
                                    <input type="number" id="monto{{ $i }}" name="pagos[{{ $i }}][monto]" data-monto step="0.01" min="0.01" class="campo" required
                                           value="{{ old("pagos.$i.monto", $i === 0 ? number_format($pedido->total, 2, '.', '') : '') }}">
                                </div>
                                <div class="col-span-2">
                                    <label class="etiqueta" for="operacion{{ $i }}">N.° de operación</label>
                                    <input type="text" id="operacion{{ $i }}" name="pagos[{{ $i }}][numero_operacion]" class="campo" maxlength="50" placeholder="Opcional" value="{{ old("pagos.$i.numero_operacion") }}">
                                </div>
                            </div>
                        </fieldset>
                    @endforeach

                    <button type="button" id="alternarPago" class="text-sm font-semibold text-orange-600 hover:text-orange-700 mb-4">
                        <i class="fas fa-plus-circle mr-1"></i> <span>Dividir en 2 pagos</span>
                    </button>

                    <div class="flex justify-between text-sm mb-4">
                        <span class="text-gray-500">Falta asignar</span>
                        <span id="restante" class="font-bold"></span>
                    </div>

                    <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-lg font-semibold transition text-sm shadow-sm">
                        <i class="fas fa-check-circle mr-2"></i> Cobrar y emitir recibo
                    </button>
                </form>

                <form method="POST" action="{{ route('cajera.pedidos.anular', $pedido->id) }}" onsubmit="return confirm('¿Anular este pedido?')" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full text-red-600 bg-red-50 hover:bg-red-100 px-6 py-2 rounded-lg font-semibold transition text-sm">
                        <i class="fas fa-ban mr-2"></i> Anular pedido
                    </button>
                </form>
            @else
                <p class="text-gray-500 text-sm">Este pedido fue anulado y no se puede cobrar.</p>
            @endif
        </div>
    </div>
@endsection

@if($pedido->estado === \App\Models\Pedido::PENDIENTE)
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const total = {{ (float) $pedido->total }};
                const segundo = document.getElementById('pago1');
                const alternar = document.getElementById('alternarPago');
                const montos = document.querySelectorAll('[data-monto]');

                function actualizar() {
                    let asignado = 0;
                    montos.forEach(function (campo) {
                        if (!campo.closest('fieldset').disabled) {
                            asignado += parseFloat(campo.value) || 0;
                        }
                    });
                    const resto = Math.round((total - asignado) * 100) / 100;
                    const etiqueta = document.getElementById('restante');
                    etiqueta.textContent = 'S/ ' + resto.toFixed(2);
                    etiqueta.className = 'font-bold ' + (resto === 0 ? 'text-green-600' : 'text-red-600');
                }

                function textoAlternar() {
                    alternar.querySelector('span').textContent = segundo.disabled ? 'Dividir en 2 pagos' : 'Usar un solo pago';
                }

                alternar.addEventListener('click', function () {
                    segundo.disabled = !segundo.disabled;
                    segundo.classList.toggle('hidden', segundo.disabled);
                    textoAlternar();
                    actualizar();
                });

                montos.forEach(campo => campo.addEventListener('input', actualizar));
                textoAlternar();
                actualizar();

                // Ticket de cocina: se abre en otra ventana para no recargar esta pestaña.
                // Reimprimir siempre pide confirmación.
                const formTicket = document.getElementById('formTicket');
                const modal = document.getElementById('modalReimprimir');
                if (formTicket && modal) {
                    let enviando = false;

                    const enviar = () => {
                        if (enviando) return; // evita doble clic rápido
                        enviando = true;
                        const ventana = 'ticket-pedido-{{ $pedido->id }}';
                        const abierta = window.open('', ventana, 'width=480,height=860');
                        formTicket.target = abierta ? ventana : '_blank';
                        formTicket.submit();
                        if (abierta) abierta.focus();

                        // Lo pendiente ya salió: el botón pasa a "Ticket" (reimprimir) sin recargar
                        formTicket.dataset.modo = 'reimprimir';
                        formTicket.querySelector('input[name=todo]').disabled = false;
                        formTicket.querySelector('[data-boton=nuevo]').classList.add('hidden');
                        formTicket.querySelector('[data-boton=reimprimir]').classList.remove('hidden');
                        setTimeout(() => { enviando = false; }, 1500);
                    };
                    const cerrar = () => modal.classList.add('hidden');

                    formTicket.addEventListener('submit', function (e) {
                        e.preventDefault();
                        if (formTicket.dataset.modo === 'nuevo') return enviar();
                        modal.classList.remove('hidden');
                        document.getElementById('confirmarReimprimir').focus();
                    });
                    document.getElementById('confirmarReimprimir').addEventListener('click', function () { cerrar(); enviar(); });
                    modal.querySelector('[data-cerrar]').addEventListener('click', cerrar);
                    modal.addEventListener('click', e => { if (e.target === modal) cerrar(); });
                    document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrar(); });
                }
            });
        </script>
    @endpush
@endif
