{{-- Selector de periodo para los reportes de finanzas.
     $periodo (App\Support\Periodo), $ruta (nombre de la ruta), $extra (parámetros que se conservan) --}}
@php
    $extra = $extra ?? [];
    $url = fn ($p) => route($ruta, $p->parametros() + $extra);
@endphp
<div class="bg-white rounded-lg p-4 md:p-5 border border-gray-200 shadow-sm mb-6 no-print">
    <div class="flex flex-wrap gap-2 mb-4">
        @foreach(\App\Support\Periodo::TIPOS as $clave => $nombre)
            @if($clave !== 'rango')
                <a href="{{ route($ruta, ['periodo' => $clave, 'fecha' => $periodo->fecha->toDateString()] + $extra) }}"
                   class="px-4 py-2 rounded-lg text-sm font-semibold border transition {{ $periodo->tipo === $clave ? 'bg-orange-500 text-white border-orange-500' : 'bg-white text-gray-700 border-gray-200 hover:border-orange-300' }}">
                    {{ $nombre }}
                </a>
            @endif
        @endforeach
        <a href="{{ route($ruta, ['periodo' => 'dia'] + $extra) }}" class="px-4 py-2 rounded-lg text-sm font-semibold border border-gray-200 text-gray-700 hover:border-orange-300">
            <i class="fas fa-calendar-day mr-1"></i> Hoy
        </a>
    </div>

    <div class="flex flex-col lg:flex-row lg:items-end gap-4">
        <div class="flex items-center gap-2">
            @if($anterior = $periodo->anterior())
                <a href="{{ $url($anterior) }}" title="Anterior" class="w-10 h-10 flex items-center justify-center rounded-lg border border-gray-200 hover:bg-orange-50 text-gray-600"><i class="fas fa-chevron-left"></i></a>
            @endif
            <span class="text-lg font-bold text-black px-2">{{ $periodo->etiqueta() }}</span>
            @if(($siguiente = $periodo->siguiente()) && $siguiente->inicio->lte(now()))
                <a href="{{ $url($siguiente) }}" title="Siguiente" class="w-10 h-10 flex items-center justify-center rounded-lg border border-gray-200 hover:bg-orange-50 text-gray-600"><i class="fas fa-chevron-right"></i></a>
            @endif
        </div>

        <form method="GET" action="{{ route($ruta) }}" class="flex flex-wrap items-end gap-2 lg:ml-auto">
            @foreach($extra as $nombre => $valor)
                <input type="hidden" name="{{ $nombre }}" value="{{ $valor }}">
            @endforeach
            @if($periodo->tipo !== 'rango')
                <input type="hidden" name="periodo" value="{{ $periodo->tipo }}">
                <div>
                    <label class="etiqueta">Ir a la fecha</label>
                    <input type="date" name="fecha" value="{{ $periodo->fecha->toDateString() }}" class="campo" onchange="this.form.submit()">
                </div>
            @else
                <input type="hidden" name="periodo" value="rango">
            @endif
        </form>

        <form method="GET" action="{{ route($ruta) }}" class="flex flex-wrap items-end gap-2">
            @foreach($extra as $nombre => $valor)
                <input type="hidden" name="{{ $nombre }}" value="{{ $valor }}">
            @endforeach
            <input type="hidden" name="periodo" value="rango">
            <div>
                <label class="etiqueta">Desde</label>
                <input type="date" name="desde" value="{{ $periodo->inicio->toDateString() }}" class="campo">
            </div>
            <div>
                <label class="etiqueta">Hasta</label>
                <input type="date" name="hasta" value="{{ $periodo->fin->toDateString() }}" class="campo">
            </div>
            <button type="submit" class="px-4 py-2.5 rounded-lg text-sm font-semibold {{ $periodo->tipo === 'rango' ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-700 border border-gray-200 hover:bg-gray-200' }}">
                <i class="fas fa-search mr-1"></i> Rango
            </button>
        </form>
    </div>
</div>

@push('scripts')
    <style>
        @media print {
            #sidebarMenu, #menuToggle, #menuOverlay, .no-print { display: none !important; }
            .md\:ml-64 { margin-left: 0 !important; }
            .fade-in { animation: none; }
            .solo-impresion { display: block !important; }
        }
        .solo-impresion { display: none; }
    </style>
@endpush
