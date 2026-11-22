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

    <!-- Totales -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
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
        <div class="{{ $resumen['balance'] >= 0 ? 'bg-orange-50 border-orange-200' : 'bg-red-100 border-red-300' }} border rounded-lg p-4">
            <p class="text-gray-700 text-sm font-semibold"><i class="fas fa-scale-balanced mr-1"></i> Balance</p>
            <p class="text-2xl md:text-3xl font-bold mt-1 {{ $resumen['balance'] >= 0 ? 'text-orange-600' : 'text-red-700' }}">S/ {{ number_format($resumen['balance'], 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">Ingresos menos egresos</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <p class="text-gray-700 text-sm font-semibold"><i class="fas fa-receipt mr-1"></i> Ticket promedio</p>
            <p class="text-2xl md:text-3xl font-bold text-black mt-1">S/ {{ number_format($resumen['promedio'], 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">Por recibo</p>
        </div>
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
                    <th class="pb-3 font-semibold text-center">Recibos</th>
                    @foreach($metodos as $nombre)
                        <th class="pb-3 font-semibold text-right hidden lg:table-cell">{{ $nombre }}</th>
                    @endforeach
                    <th class="pb-3 font-semibold text-right">Ingresos</th>
                    <th class="pb-3 font-semibold text-right">Egresos</th>
                    <th class="pb-3 font-semibold text-right">Balance</th>
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
                        <td class="py-3 text-center text-gray-700">{{ $fila['recibos'] }}</td>
                        @foreach($metodos as $clave => $nombre)
                            <td class="py-3 text-right text-gray-600 hidden lg:table-cell">{{ $fila['metodos'][$clave] > 0 ? number_format($fila['metodos'][$clave], 2) : '—' }}</td>
                        @endforeach
                        <td class="py-3 text-right font-semibold text-green-700">S/ {{ number_format($fila['ingresos'], 2) }}</td>
                        <td class="py-3 text-right text-red-600">{{ $fila['egresos'] > 0 ? 'S/ '.number_format($fila['egresos'], 2) : '—' }}</td>
                        <td class="py-3 text-right font-bold {{ $fila['balance'] >= 0 ? 'text-black' : 'text-red-700' }}">S/ {{ number_format($fila['balance'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="{{ 5 + count($metodos) }}" class="py-8 text-center text-gray-400">No hubo ingresos ni egresos en este periodo.</td></tr>
                @endforelse
            </tbody>
            @if(count($filas) > 1)
                <tfoot>
                    <tr class="border-t-2 border-gray-300 font-bold">
                        <td class="pt-3">TOTAL</td>
                        <td class="pt-3 text-center">{{ $resumen['recibos'] }}</td>
                        @foreach($metodos as $clave => $nombre)
                            <td class="pt-3 text-right hidden lg:table-cell">{{ number_format($resumen['por_metodo'][$clave] ?? 0, 2) }}</td>
                        @endforeach
                        <td class="pt-3 text-right text-green-700">S/ {{ number_format($resumen['ingresos'], 2) }}</td>
                        <td class="pt-3 text-right text-red-600">S/ {{ number_format($resumen['egresos'], 2) }}</td>
                        <td class="pt-3 text-right">S/ {{ number_format($resumen['balance'], 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endsection
