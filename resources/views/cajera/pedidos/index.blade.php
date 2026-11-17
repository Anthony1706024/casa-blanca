@extends('layouts.panel', ['nav' => 'cajera.nav'])

@section('titulo', 'Pedidos por Cobrar')
@section('icono', 'fa-cash-register')
@section('subtitulo', 'Busca por el DNI del cliente')

@section('contenido')
    <form method="GET" action="{{ route('cajera.pedidos.index') }}" class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm mb-6 flex flex-col sm:flex-row gap-4 sm:items-end">
        <div class="flex-1">
            <label class="etiqueta" for="dni">DNI del cliente</label>
            <input type="text" id="dni" name="dni" value="{{ request('dni') }}" class="campo" inputmode="numeric" maxlength="8" autofocus>
        </div>
        <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2.5 rounded-lg font-semibold transition text-sm">
            <i class="fas fa-search mr-2"></i> Buscar
        </button>
    </form>

    <div class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-gray-500">
                    <th class="pb-3 font-semibold">Pedido</th>
                    <th class="pb-3 font-semibold">DNI</th>
                    <th class="pb-3 font-semibold">Detalle</th>
                    <th class="pb-3 font-semibold text-right">Total</th>
                    <th class="pb-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($pedidos as $pedido)
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition align-top">
                        <td class="py-3">
                            <span class="text-orange-600 font-mono font-medium">{{ $pedido->numero }}</span>
                            <div class="text-gray-400 text-xs">{{ $pedido->created_at->format('d/m H:i') }} · {{ $pedido->mozo->name ?? 'Cliente' }}</div>
                        </td>
                        <td class="py-3 text-black font-semibold">
                            {{ $pedido->dni }}
                            @if($pedido->cliente_nombre)<div class="text-gray-400 text-xs font-normal">{{ $pedido->cliente_nombre }}</div>@endif
                        </td>
                        <td class="py-3 text-gray-600">
                            @foreach($pedido->items as $item)
                                <div>{{ $item->cantidad }} × {{ $item->producto_nombre }}</div>
                            @endforeach
                        </td>
                        <td class="py-3 text-right text-black font-semibold whitespace-nowrap">S/ {{ number_format($pedido->total, 2) }}</td>
                        <td class="py-3 text-right">
                            <a href="{{ route('cajera.pedidos.show', $pedido->id) }}" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-semibold transition text-xs whitespace-nowrap">
                                <i class="fas fa-money-bill-wave mr-1"></i> Cobrar
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-8 text-center text-gray-400">No hay pedidos pendientes{{ request('dni') ? ' para ese DNI' : '' }}.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $pedidos->links() }}</div>
    </div>
@endsection
