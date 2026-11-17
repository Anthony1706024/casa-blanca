@extends('layouts.panel', ['nav' => 'admin.nav'])

@section('titulo', 'Historial de Cambios')
@section('icono', 'fa-history')
@section('subtitulo', 'Quién cambió cada precio y cuándo')

@section('contenido')
    @include('admin.precios._tabs')

    <form method="GET" action="{{ route('admin.precios.historial') }}" class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm mb-6 flex flex-col sm:flex-row gap-4 sm:items-end">
        <div class="flex-1">
            <label class="etiqueta">Producto</label>
            <input type="text" name="search" value="{{ request('search') }}" class="campo">
        </div>
        <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2.5 rounded-lg font-semibold transition text-sm">
            <i class="fas fa-search mr-2"></i> Buscar
        </button>
    </form>

    <div class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-gray-500">
                    <th class="pb-3 font-semibold">Fecha</th>
                    <th class="pb-3 font-semibold">Producto</th>
                    <th class="pb-3 font-semibold text-right">Antes</th>
                    <th class="pb-3 font-semibold text-right">Después</th>
                    <th class="pb-3 font-semibold hidden sm:table-cell">Usuario</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cambios as $cambio)
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                        <td class="py-3 text-gray-600 whitespace-nowrap">{{ $cambio->created_at->format('d/m/Y H:i') }}</td>
                        <td class="py-3 text-black font-medium">{{ $cambio->producto->nombre }}</td>
                        <td class="py-3 text-right text-gray-600 whitespace-nowrap">{{ $cambio->precio_anterior !== null ? 'S/ '.number_format($cambio->precio_anterior, 2) : 'Sin precio' }}</td>
                        <td class="py-3 text-right font-semibold whitespace-nowrap">{{ $cambio->precio_nuevo !== null ? 'S/ '.number_format($cambio->precio_nuevo, 2) : 'Sin precio' }}</td>
                        <td class="py-3 text-gray-600 hidden sm:table-cell">{{ $cambio->usuario->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-8 text-center text-gray-400">Aún no hay cambios de precio registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $cambios->links() }}</div>
    </div>
@endsection
