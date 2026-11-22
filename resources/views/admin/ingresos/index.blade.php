@extends('layouts.panel', ['nav' => 'admin.nav'])

@section('titulo', 'Ingresos Diarios')
@section('icono', 'fa-chart-line')
@section('subtitulo', 'Lo cobrado por caja, con los egresos del mismo periodo y lo que queda')

@section('acciones')
    <a href="{{ route('admin.ingresos.exportar', $periodo->parametros()) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold transition text-sm shadow-sm no-print">
        <i class="fas fa-file-excel mr-1"></i> Descargar Excel
    </a>
    <button type="button" onclick="window.print()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold transition text-sm border border-gray-200 no-print">
        <i class="fas fa-print mr-1"></i> Imprimir
    </button>
@endsection

@section('contenido')
    @include('admin.finanzas._periodo', ['ruta' => 'admin.ingresos.index'])

    <h2 class="solo-impresion text-xl font-bold mb-4">Casa Blanca · Ingresos · {{ $periodo->etiqueta() }}</h2>

    <!-- Saldo inicial de caja -->
    <div class="bg-white rounded-lg p-4 md:p-6 border border-blue-200 shadow-sm mb-6 no-print">
        <h3 class="font-bold text-black mb-1"><i class="fas fa-cash-register text-blue-500 mr-2"></i>Saldo inicial de caja</h3>
        <p class="text-sm text-gray-500 mb-4">Con cuánto abre la caja el día. Si ese día ya tiene saldo, se reemplaza.</p>
        <form method="POST" action="{{ route('admin.ingresos.saldo.guardar') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
            @csrf
            <div>
                <label class="etiqueta">Fecha *</label>
                <input type="date" name="fecha" required max="{{ now()->toDateString() }}" class="campo" value="{{ old('fecha', $fechaSaldo->toDateString()) }}">
            </div>
            <div>
                <label class="etiqueta">Saldo inicial (S/) *</label>
                <input type="number" name="monto" required step="0.01" min="0" class="campo" placeholder="0.00" value="{{ old('monto', $saldoDelDia?->monto) }}">
            </div>
            <div>
                <label class="etiqueta">Nota</label>
                <input type="text" name="nota" maxlength="255" class="campo" placeholder="Opcional, ej.: sencillo para vueltos" value="{{ old('nota', $saldoDelDia?->nota) }}">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg font-semibold transition text-sm">
                <i class="fas fa-save mr-1"></i> Guardar saldo
            </button>
        </form>

        @if($saldos->isNotEmpty())
            <div class="mt-4 border-t border-gray-100 pt-3">
                <p class="text-xs text-gray-500 font-semibold uppercase mb-2">Saldos de este periodo</p>
                <div class="divide-y divide-gray-100">
                    @foreach($saldos as $saldo)
                        <div class="flex justify-between items-center py-1.5 text-sm gap-3">
                            <span class="text-gray-700">
                                {{ ucfirst($saldo->fecha->locale('es')->isoFormat('ddd')) }} {{ $saldo->fecha->format('d/m/Y') }}
                                @if($saldo->nota)<span class="text-gray-400">· {{ $saldo->nota }}</span>@endif
                            </span>
                            <span class="flex items-center gap-3">
                                <strong class="text-blue-700">S/ {{ number_format($saldo->monto, 2) }}</strong>
                                <form method="POST" action="{{ route('admin.ingresos.saldo.eliminar', $saldo->id) }}" onsubmit="return confirm({{ \Illuminate\Support\Js::from('¿Eliminar el saldo inicial del '.$saldo->fecha->format('d/m/Y').'?') }})">
                                    @csrf @method('DELETE')
                                    <button type="submit" title="Eliminar saldo" class="text-red-500 hover:text-red-700 px-1"><i class="fas fa-times"></i></button>
                                </form>
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Totales -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-blue-700 text-sm font-semibold"><i class="fas fa-cash-register mr-1"></i> Saldo inicial</p>
            <p class="text-2xl md:text-3xl font-bold text-blue-700 mt-1">{{ $resumen['saldo_inicial'] !== null ? 'S/ '.number_format($resumen['saldo_inicial'], 2) : '—' }}</p>
            <p class="text-xs text-blue-700/80 mt-1">{{ $resumen['saldo_fecha'] ? 'Del '.$resumen['saldo_fecha']->format('d/m/Y') : 'Sin registrar' }}</p>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <p class="text-green-700 text-sm font-semibold"><i class="fas fa-arrow-down mr-1"></i> Ingresos</p>
            <p class="text-2xl md:text-3xl font-bold text-green-700 mt-1">S/ {{ number_format($resumen['ingresos'], 2) }}</p>
            <p class="text-xs text-green-700/80 mt-1">{{ $resumen['recibos'] }} {{ $resumen['recibos'] === 1 ? 'recibo' : 'recibos' }}</p>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <p class="text-red-700 text-sm font-semibold"><i class="fas fa-arrow-up mr-1"></i> Egresos</p>
            <p class="text-2xl md:text-3xl font-bold text-red-700 mt-1">S/ {{ number_format($resumen['egresos'], 2) }}</p>
            <a href="{{ route('admin.egresos.index', $periodo->parametros()) }}" class="text-xs text-red-700/80 mt-1 underline no-print">Ver egresos</a>
        </div>
        <div class="{{ $resumen['saldo_final'] >= 0 ? 'bg-orange-50 border-orange-200' : 'bg-red-100 border-red-300' }} border rounded-lg p-4">
            <p class="text-gray-700 text-sm font-semibold"><i class="fas fa-wallet mr-1"></i> Saldo final</p>
            <p class="text-2xl md:text-3xl font-bold mt-1 {{ $resumen['saldo_final'] >= 0 ? 'text-orange-600' : 'text-red-700' }}">S/ {{ number_format($resumen['saldo_final'], 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">Saldo inicial + ingresos − egresos</p>
        </div>
    </div>
    <div class="flex flex-wrap gap-x-8 gap-y-2 text-sm text-gray-600 mb-6 -mt-2 px-1">
        <span><i class="fas fa-scale-balanced text-gray-400 mr-1"></i> Balance (ingresos − egresos): <strong class="{{ $resumen['balance'] >= 0 ? 'text-black' : 'text-red-700' }}">S/ {{ number_format($resumen['balance'], 2) }}</strong></span>
        <span><i class="fas fa-receipt text-gray-400 mr-1"></i> Ticket promedio: <strong class="text-black">S/ {{ number_format($resumen['promedio'], 2) }}</strong></span>
    </div>

    <!-- Por forma de pago -->
    <div class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm mb-6">
        <h3 class="font-bold text-black mb-4">Cobrado por forma de pago</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            @foreach($metodos as $clave => $nombre)
                @php $monto = $resumen['por_metodo'][$clave] ?? 0; $porcentaje = $resumen['ingresos'] > 0 ? $monto / $resumen['ingresos'] * 100 : 0; @endphp
                <div class="border border-gray-100 rounded-lg p-3">
                    <p class="text-sm text-gray-600">{{ $nombre }}</p>
                    <p class="text-lg font-bold text-black">S/ {{ number_format($monto, 2) }}</p>
                    <div class="h-1.5 bg-gray-100 rounded mt-2"><div class="h-1.5 bg-orange-500 rounded" style="width: {{ round($porcentaje, 1) }}%"></div></div>
                    <p class="text-xs text-gray-400 mt-1">{{ number_format($porcentaje, 0) }}%</p>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Detalle por día o por mes -->
    <div class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm overflow-x-auto">
        <h3 class="font-bold text-black mb-4">{{ $periodo->agrupaPorMes() ? 'Por mes' : 'Por día' }}</h3>
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-gray-500">
                    <th class="pb-3 font-semibold">{{ $periodo->agrupaPorMes() ? 'Mes' : 'Fecha' }}</th>
                    <th class="pb-3 font-semibold text-right">Saldo inicial</th>
                    <th class="pb-3 font-semibold text-center">Recibos</th>
                    @foreach($metodos as $nombre)
                        <th class="pb-3 font-semibold text-right hidden lg:table-cell">{{ $nombre }}</th>
                    @endforeach
                    <th class="pb-3 font-semibold text-right">Ingresos</th>
                    <th class="pb-3 font-semibold text-right">Egresos</th>
                    <th class="pb-3 font-semibold text-right">Balance</th>
                    <th class="pb-3 font-semibold text-right">Saldo final</th>
                </tr>
            </thead>
            <tbody>
                @forelse($filas as $fila)
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                        <td class="py-3 font-medium">
                            @if($periodo->agrupaPorMes())
                                <a href="{{ route('admin.ingresos.index', ['periodo' => 'mes', 'fecha' => $fila['fecha']->toDateString()]) }}" class="text-orange-600 hover:underline">{{ \App\Support\Periodo::nombreMes($fila['fecha']) }}</a>
                            @else
                                <a href="{{ route('admin.ventas.index', ['fecha' => $fila['fecha']->toDateString()]) }}" class="text-orange-600 hover:underline" title="Ver los recibos del día">
                                    {{ ucfirst($fila['fecha']->locale('es')->isoFormat('ddd')) }} {{ $fila['fecha']->format('d/m/Y') }}
                                </a>
                            @endif
                        </td>
                        <td class="py-3 text-right text-blue-700">{{ $fila['saldo_inicial'] !== null ? 'S/ '.number_format($fila['saldo_inicial'], 2) : '—' }}</td>
                        <td class="py-3 text-center text-gray-700">{{ $fila['recibos'] }}</td>
                        @foreach($metodos as $clave => $nombre)
                            <td class="py-3 text-right text-gray-600 hidden lg:table-cell">{{ $fila['metodos'][$clave] > 0 ? number_format($fila['metodos'][$clave], 2) : '—' }}</td>
                        @endforeach
                        <td class="py-3 text-right font-semibold text-green-700">S/ {{ number_format($fila['ingresos'], 2) }}</td>
                        <td class="py-3 text-right text-red-600">{{ $fila['egresos'] > 0 ? 'S/ '.number_format($fila['egresos'], 2) : '—' }}</td>
                        <td class="py-3 text-right font-bold {{ $fila['balance'] >= 0 ? 'text-black' : 'text-red-700' }}">S/ {{ number_format($fila['balance'], 2) }}</td>
                        <td class="py-3 text-right font-bold text-orange-600">{{ $fila['saldo_final'] !== null ? 'S/ '.number_format($fila['saldo_final'], 2) : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="{{ 7 + count($metodos) }}" class="py-8 text-center text-gray-400">No hubo ingresos ni egresos en este periodo.</td></tr>
                @endforelse
            </tbody>
            @if(count($filas) > 1)
                <tfoot>
                    <tr class="border-t-2 border-gray-300 font-bold">
                        <td class="pt-3">TOTAL</td>
                        <td class="pt-3 text-right text-blue-700">{{ $resumen['saldo_inicial'] !== null ? 'S/ '.number_format($resumen['saldo_inicial'], 2) : '—' }}</td>
                        <td class="pt-3 text-center">{{ $resumen['recibos'] }}</td>
                        @foreach($metodos as $clave => $nombre)
                            <td class="pt-3 text-right hidden lg:table-cell">{{ number_format($resumen['por_metodo'][$clave] ?? 0, 2) }}</td>
                        @endforeach
                        <td class="pt-3 text-right text-green-700">S/ {{ number_format($resumen['ingresos'], 2) }}</td>
                        <td class="pt-3 text-right text-red-600">S/ {{ number_format($resumen['egresos'], 2) }}</td>
                        <td class="pt-3 text-right">S/ {{ number_format($resumen['balance'], 2) }}</td>
                        <td class="pt-3 text-right text-orange-600">S/ {{ number_format($resumen['saldo_final'], 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endsection
