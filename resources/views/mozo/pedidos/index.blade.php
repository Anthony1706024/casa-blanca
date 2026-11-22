@extends('layouts.panel', ['nav' => 'mozo.nav'])

@section('titulo', 'Pedidos')
@section('icono', 'fa-clipboard-list')
@section('subtitulo', 'Pedidos del mozo y prepedidos de clientes')

@section('acciones')
    <a href="{{ route('mozo.pedidos.create') }}" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-semibold transition text-sm shadow-sm">
        <i class="fas fa-plus-circle mr-1"></i> Nuevo
    </a>
@endsection

@section('contenido')
    <form method="GET" action="{{ route('mozo.pedidos.index') }}" class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
        <div>
            <label class="etiqueta">DNI</label>
            <input type="text" name="dni" value="{{ request('dni') }}" class="campo" maxlength="8">
        </div>
        <div>
            <label class="etiqueta">Estado</label>
            <select name="estado" class="campo">
                <option value="">Todos</option>
                @foreach(['pendiente' => 'Pendiente', 'pagado' => 'Pagado', 'anulado' => 'Anulado'] as $valor => $texto)
                    <option value="{{ $valor }}" {{ request('estado') === $valor ? 'selected' : '' }}>{{ $texto }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2.5 rounded-lg font-semibold transition text-sm">
            <i class="fas fa-search mr-2"></i> Buscar
        </button>
    </form>

    <div class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-gray-500">
                    <th class="pb-3 font-semibold">Pedido</th>
                    <th class="pb-3 font-semibold">DNI</th>
                    <th class="pb-3 font-semibold">Mesa</th>
                    <th class="pb-3 font-semibold">Detalle</th>
                    <th class="pb-3 font-semibold text-right">Total</th>
                    <th class="pb-3 font-semibold text-center">Estado</th>
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
                        <td class="py-3 text-black">{{ $pedido->dni }}</td>
                        <td class="py-3 text-black font-semibold">{{ $pedido->mesa ?? '-' }}</td>
                        <td class="py-3 text-gray-600">
                            @foreach($pedido->items as $item)
                                <div>{{ $item->cantidad }} × {{ $item->producto_nombre }}</div>
                            @endforeach
                        </td>
                        <td class="py-3 text-right text-black font-semibold whitespace-nowrap">S/ {{ number_format($pedido->total, 2) }}</td>
                        <td class="py-3 text-center">@include('pedidos._estado', ['estado' => $pedido->estado])</td>
                        <td class="py-3 text-right">
                            @if($pedido->estado === \App\Models\Pedido::PENDIENTE)
                                <form method="POST" action="{{ route('mozo.pedidos.anular', $pedido->id) }}" onsubmit="return confirm('¿Anular este pedido?')">
                                    @csrf
                                    <button type="submit" title="Anular" class="text-red-600 bg-red-50 hover:bg-red-100 px-2 py-1 rounded"><i class="fas fa-ban"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-gray-400">No hay pedidos.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $pedidos->links() }}</div>
    </div>
@endsection
