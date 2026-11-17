@extends('layouts.panel', ['nav' => 'admin.nav'])

@section('titulo', 'Ventas por Precio')
@section('icono', 'fa-chart-bar')
@section('subtitulo', 'Cuánto se vendió realmente a cada precio; no cambia aunque subas el precio después')

@section('contenido')
    @include('admin.precios._tabs')

    <form method="GET" action="{{ route('admin.precios.ventas') }}" class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm mb-6 grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
        <div>
            <label class="etiqueta">Producto</label>
            <input type="text" name="search" value="{{ request('search') }}" class="campo">
        </div>
        <div>
            <label class="etiqueta">Desde</label>
            <input type="date" name="desde" value="{{ request('desde') }}" class="campo">
        </div>
        <div>
            <label class="etiqueta">Hasta</label>
            <input type="date" name="hasta" value="{{ request('hasta') }}" class="campo">
        </div>
        <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2.5 rounded-lg font-semibold transition text-sm">
            <i class="fas fa-search mr-2"></i> Buscar
        </button>
    </form>

    <div class="bg-orange-50 border border-orange-200 rounded-lg px-6 py-4 mb-6 flex justify-between items-center">
        <span class="text-gray-700 font-semibold">Total vendido en este filtro</span>
        <span class="text-2xl font-bold text-orange-600">S/ {{ number_format($total, 2) }}</span>
    </div>

    <div class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-gray-500">
                    <th class="pb-3 font-semibold">Producto</th>
                    <th class="pb-3 font-semibold text-right">Precio vendido</th>
                    <th class="pb-3 font-semibold text-right">Unidades</th>
                    <th class="pb-3 font-semibold text-right">Importe</th>
                    <th class="pb-3 font-semibold hidden md:table-cell">Vendido entre</th>
                    <th class="pb-3 font-semibold text-right">Precio actual</th>
                </tr>
            </thead>
            <tbody>
                @forelse($filas as $fila)
                    @php($cambio = (float) $fila->precio_actual !== (float) $fila->precio_unitario)
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                        <td class="py-3 text-black font-medium">{{ $fila->producto_nombre }}</td>
                        <td class="py-3 text-right font-semibold whitespace-nowrap">S/ {{ number_format($fila->precio_unitario, 2) }}</td>
                        <td class="py-3 text-right text-gray-700">{{ $fila->unidades }}</td>
                        <td class="py-3 text-right text-black font-semibold whitespace-nowrap">S/ {{ number_format($fila->importe, 2) }}</td>
                        <td class="py-3 text-gray-500 text-xs hidden md:table-cell">{{ \Illuminate\Support\Carbon::parse($fila->primera_venta)->format('d/m/Y') }} – {{ \Illuminate\Support\Carbon::parse($fila->ultima_venta)->format('d/m/Y') }}</td>
                        <td class="py-3 text-right whitespace-nowrap {{ $cambio ? 'text-orange-600 font-semibold' : 'text-gray-500' }}">
                            {{ $fila->precio_actual !== null ? 'S/ '.number_format($fila->precio_actual, 2) : 'Sin precio' }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-8 text-center text-gray-400">No hay ventas cobradas en este filtro.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $filas->links() }}</div>
    </div>
@endsection
