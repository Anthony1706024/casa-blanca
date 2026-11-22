@extends('layouts.panel', ['nav' => 'cajera.nav'])

@section('titulo', 'Pedidos por Cobrar')
@section('icono', 'fa-cash-register')
@section('subtitulo', 'Se actualiza sola: los pedidos nuevos suenan y aparecen resaltados')

@section('acciones')
    <a href="{{ route('cajera.pedidos.create') }}" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-semibold transition text-sm shadow-sm">
        <i class="fas fa-plus-circle mr-1"></i> Nuevo pedido
    </a>
@endsection

@section('contenido')
    <form method="GET" action="{{ route('cajera.pedidos.index') }}" class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm mb-6 flex flex-col sm:flex-row gap-4 sm:items-end">
        <div class="flex-1">
            <label class="etiqueta" for="dni">DNI o número de mesa</label>
            <input type="text" id="dni" name="dni" value="{{ request('dni') }}" class="campo" maxlength="20" autofocus>
        </div>
        <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2.5 rounded-lg font-semibold transition text-sm">
            <i class="fas fa-search mr-2"></i> Buscar
        </button>
    </form>

    @if(session('recibo_id'))
        {{-- Recién cobrado: el comprobante se abre en otra ventana, sin salir de esta lista --}}
        <div class="bg-white border border-gray-200 rounded-lg p-4 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-sm">
            <span class="text-gray-700">
                <i class="fas fa-receipt text-orange-500 mr-1"></i>
                {{ session('recibo_imprimir') ? 'Imprime el comprobante desde el navegador:' : 'Puedes ver o reimprimir el comprobante:' }}
            </span>
            <a href="{{ route('recibos.show', ['id' => session('recibo_id'), 'imprimir' => session('recibo_imprimir') ? 1 : null]) }}"
               target="recibo-{{ session('recibo_id') }}"
               onclick="window.open(this.href, this.target, 'width=480,height=860'); return false;"
               class="bg-gray-900 hover:bg-black text-white px-4 py-2 rounded-lg font-semibold text-center whitespace-nowrap">
                <i class="fas fa-external-link-alt mr-1"></i> Abrir comprobante
            </a>
        </div>
    @endif

    {{-- Todos los pendientes, aunque estén en otra página: con esto se avisan los pedidos nuevos --}}
    <div data-auto="avisos" hidden>@foreach($idsPendientes as $idPendiente)<span data-aviso-id="{{ $idPendiente }}"></span>@endforeach</div>

    <div class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm overflow-x-auto" data-auto="lista">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-gray-500">
                    <th class="pb-3 font-semibold">Pedido</th>
                    <th class="pb-3 font-semibold">DNI</th>
                    <th class="pb-3 font-semibold">Mesa</th>
                    <th class="pb-3 font-semibold">Detalle</th>
                    <th class="pb-3 font-semibold text-right">Total</th>
                    <th class="pb-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($pedidos as $pedido)
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition align-top" data-pedido-id="{{ $pedido->id }}">
                        <td class="py-3">
                            <span class="text-orange-600 font-mono font-medium">{{ $pedido->numero }}</span>
                            <div class="text-gray-400 text-xs">{{ $pedido->created_at->format('d/m H:i') }} · {{ $pedido->mozo->name ?? 'Cliente' }}</div>
                        </td>
                        <td class="py-3 text-black font-semibold">
                            {{ $pedido->dni ?? '—' }}
                            @if($pedido->cliente_nombre)<div class="text-gray-400 text-xs font-normal">{{ $pedido->cliente_nombre }}</div>@endif
                        </td>
                        <td class="py-3 text-black font-semibold">{{ $pedido->mesa ?? '-' }}</td>
                        <td class="py-3 text-gray-600">
                            @foreach($pedido->items as $item)
                                <div>{{ $item->cantidad }} × {{ $item->producto_nombre }}</div>
                            @endforeach
                        </td>
                        <td class="py-3 text-right text-black font-semibold whitespace-nowrap">S/ {{ number_format($pedido->total, 2) }}</td>
                        <td class="py-3 text-right">
                            <a href="{{ route('cajera.pedidos.show', $pedido->id) }}" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-semibold transition text-xs whitespace-nowrap">
                                <i class="fas fa-folder-open mr-1"></i> Abrir
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"class="py-8 text-center text-gray-400">No hay pedidos pendientes{{ request('dni') ? ' para ese DNI o mesa' : '' }}.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $pedidos->links() }}</div>
    </div>

    @include('partials.auto-actualizar', ['avisar' => true])
@endsection
