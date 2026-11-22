@extends('layouts.panel', ['nav' => $nav])

@section('titulo', 'Impresiones')
@section('icono', 'fa-print')
@section('subtitulo', 'Vouchers y tickets de cocina enviados a la ticketera (últimos 50)')
@section('ancho', 'max-w-5xl')

@section('acciones')
    <a href="{{ route('impresiones.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold transition text-sm border border-gray-200">
        <i class="fas fa-sync-alt mr-1"></i> Actualizar
    </a>
@endsection

@section('contenido')
    @if($atascadas)
        <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg mb-6 flex items-center gap-3 text-sm">
            <i class="fas fa-exclamation-triangle text-amber-500"></i>
            La ticketera no está recibiendo: revisa que la PC de caja esté prendida y el agente corriendo.
        </div>
    @endif

    @unless($enCola)
        <div class="bg-gray-50 border border-gray-200 text-gray-600 px-4 py-3 rounded-lg mb-6 text-sm">
            <i class="fas fa-info-circle mr-1"></i>
            La cola de impresión no está activa en este servidor (TICKETERA_MODO=cola y TICKETERA_CLAVE). Los tickets se imprimen de otra forma.
        </div>
    @endunless

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-left">
                <tr>
                    <th class="px-4 py-3 font-semibold">Fecha</th>
                    <th class="px-4 py-3 font-semibold">Descripción</th>
                    <th class="px-4 py-3 font-semibold">Estado</th>
                    <th class="px-4 py-3 font-semibold">Error</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($impresiones as $impresion)
                    @php
                        $color = [
                            \App\Models\Impresion::PENDIENTE => 'bg-amber-100 text-amber-800',
                            \App\Models\Impresion::ENVIANDO => 'bg-blue-100 text-blue-800',
                            \App\Models\Impresion::IMPRESA => 'bg-green-100 text-green-800',
                            \App\Models\Impresion::ERROR => 'bg-red-100 text-red-800',
                        ][$impresion->estado] ?? 'bg-gray-100 text-gray-700';
                    @endphp
                    <tr class="border-t border-gray-100 align-top">
                        <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                            {{ $impresion->created_at->format('d/m H:i:s') }}
                            @if($impresion->impresa_en)
                                <div class="text-xs text-gray-400">Impresa {{ $impresion->impresa_en->format('H:i:s') }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-black">
                            <i class="fas {{ $impresion->tipo === \App\Models\Impresion::VOUCHER ? 'fa-receipt' : 'fa-utensils' }} text-gray-400 mr-1"></i>
                            {{ $impresion->descripcion }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $color }}">{{ $impresion->estado_nombre }}</span>
                            @if($impresion->intentos > 0)
                                <div class="text-xs text-gray-400 mt-1">{{ $impresion->intentos }} {{ $impresion->intentos === 1 ? 'intento' : 'intentos' }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-red-700 text-xs max-w-xs break-words">{{ $impresion->error }}</td>
                        <td class="px-4 py-3 text-right">
                            @if($impresion->estado === \App\Models\Impresion::ERROR)
                                <form method="POST" action="{{ route('impresiones.reintentar', $impresion->id) }}">
                                    @csrf
                                    <button type="submit" class="bg-gray-900 hover:bg-black text-white px-3 py-1.5 rounded-lg font-semibold text-xs whitespace-nowrap">
                                        <i class="fas fa-redo mr-1"></i> Reintentar
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Todavía no se envió nada a la ticketera.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
    {{-- Se actualiza sola para ver cómo avanza la cola --}}
    <script>setTimeout(function () { window.location.reload(); }, 15000);</script>
@endpush
