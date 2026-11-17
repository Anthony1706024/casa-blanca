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
                        <td class="py-2 text-black">{{ $item->producto_nombre }}</td>
                        <td class="py-2 text-right whitespace-nowrap align-top">S/ {{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </table>

            <div class="flex justify-between text-xl font-bold mt-4">
                <span>Total</span>
                <span class="text-orange-600">S/ {{ number_format($pedido->total, 2) }}</span>
            </div>
        </div>

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
                                    <input type="text" id="operacion{{ $i }}" name="pagos[{{ $i }}][numero_operacion]" data-operacion class="campo" maxlength="50" value="{{ old("pagos.$i.numero_operacion") }}">
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

                // En efectivo el número de operación es opcional
                document.querySelectorAll('[data-pago]').forEach(function (pago) {
                    const metodo = pago.querySelector('[data-metodo]');
                    const operacion = pago.querySelector('[data-operacion]');
                    const aplicar = () => {
                        operacion.required = metodo.value !== 'efectivo';
                        operacion.placeholder = metodo.value === 'efectivo' ? 'Opcional en efectivo' : 'Obligatorio';
                    };
                    metodo.addEventListener('change', aplicar);
                    aplicar();
                });

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
            });
        </script>
    @endpush
@endif
