<div class="flex flex-wrap gap-2 mb-6">
    @foreach(['admin.precios.index' => ['fa-tags', 'Precios y Detalle'], 'admin.precios.historial' => ['fa-history', 'Historial de Cambios'], 'admin.precios.ventas' => ['fa-chart-bar', 'Ventas por Precio']] as $ruta => [$icono, $texto])
        <a href="{{ route($ruta) }}" class="px-4 py-2 rounded-lg font-semibold text-sm transition {{ request()->routeIs($ruta) ? 'bg-orange-500 text-white shadow-sm' : 'bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-200' }}">
            <i class="fas {{ $icono }} mr-1"></i> {{ $texto }}
        </a>
    @endforeach
</div>
