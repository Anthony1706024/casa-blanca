@extends('layouts.panel', ['nav' => 'admin.nav'])

@section('titulo', 'Egresos')
@section('icono', 'fa-file-invoice-dollar')
@section('subtitulo', 'Compras, pagos a distribuidores y al personal, arreglos, servicios y otros gastos')

@php $filtros = array_filter(request()->only('categoria', 'buscar'), fn ($v) => $v !== null && $v !== ''); @endphp

@section('acciones')
    <a href="{{ route('admin.egresos.exportar', $periodo->parametros() + $filtros) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold transition text-sm shadow-sm no-print">
        <i class="fas fa-file-excel mr-1"></i> Descargar Excel
    </a>
    <button type="button" onclick="window.print()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold transition text-sm border border-gray-200 no-print">
        <i class="fas fa-print mr-1"></i> Imprimir
    </button>
    <a href="{{ route('admin.ingresos.index', $periodo->parametros()) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold transition text-sm border border-gray-200 no-print">
        <i class="fas fa-chart-line mr-1"></i> Ingresos
    </a>
@endsection

@section('contenido')
    <!-- Registrar -->
    <form method="POST" action="{{ route('admin.egresos.store') }}" class="bg-white rounded-lg p-4 md:p-6 border border-orange-200 shadow-sm mb-6 no-print">
        @csrf
        <h3 class="font-bold text-black mb-4"><i class="fas fa-plus-circle text-orange-500 mr-2"></i>Registrar egreso</h3>
        @include('admin.egresos._form', ['egreso' => null])
        <div class="mt-4 flex justify-end">
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2.5 rounded-lg font-semibold transition text-sm shadow-sm">
                <i class="fas fa-save mr-2"></i> Guardar egreso
            </button>
        </div>
    </form>

    @include('admin.finanzas._periodo', ['ruta' => 'admin.egresos.index', 'extra' => $filtros])

    <h2 class="solo-impresion text-xl font-bold mb-4">Casa Blanca · Egresos · {{ $periodo->etiqueta() }}</h2>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Total y por tipo -->
        <div class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm lg:col-span-2">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-black">Total por tipo</h3>
                <span class="text-2xl font-bold text-red-600">S/ {{ number_format(array_sum(array_column($porCategoria, 'total')), 2) }}</span>
            </div>
            @php $mayor = max(array_column($porCategoria, 'total') ?: [0]); @endphp
            @forelse($porCategoria as $fila)
                <a href="{{ route('admin.egresos.index', $periodo->parametros() + ['categoria' => $fila['id']]) }}" class="block py-2 hover:bg-gray-50 rounded px-2 -mx-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-700">{{ $fila['nombre'] }} <span class="text-gray-400">({{ $fila['cantidad'] }})</span></span>
                        <span class="font-semibold text-black">S/ {{ number_format($fila['total'], 2) }}</span>
                    </div>
                    <div class="h-1.5 bg-gray-100 rounded mt-1"><div class="h-1.5 bg-red-400 rounded" style="width: {{ $mayor > 0 ? round($fila['total'] / $mayor * 100, 1) : 0 }}%"></div></div>
                </a>
            @empty
                <p class="text-gray-400 text-sm py-4 text-center">No hay egresos en este periodo.</p>
            @endforelse
        </div>

        <!-- Tipos de egreso -->
        <div class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm no-print">
            <h3 class="font-bold text-black mb-3">Tipos de egreso</h3>
            <form method="POST" action="{{ route('admin.egresos.categorias.store') }}" class="flex gap-2 mb-4">
                @csrf
                <input type="text" name="nombre" required maxlength="80" class="campo" placeholder="Nuevo tipo, ej.: Luz">
                <button type="submit" title="Agregar tipo" class="bg-orange-500 hover:bg-orange-600 text-white px-3 rounded-lg"><i class="fas fa-plus"></i></button>
            </form>
            <div class="max-h-72 overflow-y-auto divide-y divide-gray-100">
                @foreach($categorias as $categoria)
                    <div class="flex justify-between items-center py-1.5 text-sm">
                        <span class="text-gray-700">{{ $categoria->nombre }}</span>
                        @if($categoria->egresos_count === 0)
                            <form method="POST" action="{{ route('admin.egresos.categorias.destroy', $categoria->id) }}" onsubmit="return confirm({{ \Illuminate\Support\Js::from('¿Eliminar el tipo "'.$categoria->nombre.'"?') }})">
                                @csrf @method('DELETE')
                                <button type="submit" title="Eliminar tipo" class="text-red-500 hover:text-red-700 px-1"><i class="fas fa-times"></i></button>
                            </form>
                        @else
                            <span class="text-xs text-gray-400">{{ $categoria->egresos_count }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Lista -->
    <div class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm overflow-x-auto">
        <form method="GET" action="{{ route('admin.egresos.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end mb-4 no-print">
            @foreach($periodo->parametros() as $nombre => $valor)
                <input type="hidden" name="{{ $nombre }}" value="{{ $valor }}">
            @endforeach
            <div>
                <label class="etiqueta">Tipo</label>
                <select name="categoria" class="campo" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria->id }}" @selected(request('categoria') == $categoria->id)>{{ $categoria->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="etiqueta">Buscar</label>
                <input type="text" name="buscar" value="{{ request('buscar') }}" class="campo" placeholder="Descripción o proveedor">
            </div>
            <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-lg font-semibold text-sm border border-gray-200">
                <i class="fas fa-search mr-1"></i> Filtrar
            </button>
        </form>

        <div class="flex justify-between items-center mb-3">
            <h3 class="font-bold text-black">Detalle</h3>
            <span class="text-sm text-gray-600">Total listado: <strong class="text-red-600 text-base">S/ {{ number_format($total, 2) }}</strong></span>
        </div>
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-gray-500">
                    <th class="pb-3 font-semibold">Fecha</th>
                    <th class="pb-3 font-semibold">Tipo</th>
                    <th class="pb-3 font-semibold">Descripción</th>
                    <th class="pb-3 font-semibold hidden md:table-cell">Proveedor</th>
                    <th class="pb-3 font-semibold hidden lg:table-cell">Pagado con</th>
                    <th class="pb-3 font-semibold text-right">Monto</th>
                    <th class="pb-3 no-print"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($egresos as $egreso)
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                        <td class="py-3 text-gray-600 whitespace-nowrap">{{ $egreso->fecha->format('d/m/Y') }}</td>
                        <td class="py-3"><span class="px-2 py-0.5 rounded bg-red-50 text-red-700 text-xs font-semibold">{{ $egreso->categoria->nombre }}</span></td>
                        <td class="py-3 text-black">
                            {{ $egreso->descripcion }}
                            @if($egreso->comprobante)<span class="block text-xs text-gray-400">Comprobante {{ $egreso->comprobante }}</span>@endif
                        </td>
                        <td class="py-3 text-gray-600 hidden md:table-cell">{{ $egreso->proveedor ?: '—' }}</td>
                        <td class="py-3 text-gray-600 hidden lg:table-cell">{{ $egreso->metodo_nombre ?: '—' }}</td>
                        <td class="py-3 text-right font-semibold text-red-600 whitespace-nowrap">S/ {{ number_format($egreso->monto, 2) }}</td>
                        <td class="py-3 text-right whitespace-nowrap no-print">
                            <a href="{{ route('admin.egresos.edit', $egreso->id) }}" title="Editar" class="text-blue-600 bg-blue-50 hover:bg-blue-100 px-2 py-1 rounded"><i class="fas fa-pen"></i></a>
                            <form method="POST" action="{{ route('admin.egresos.destroy', $egreso->id) }}" class="inline"
                                  onsubmit="return confirm({{ \Illuminate\Support\Js::from('¿Eliminar el egreso "'.$egreso->descripcion.'" de S/ '.number_format($egreso->monto, 2).'?') }})">
                                @csrf @method('DELETE')
                                <button type="submit" title="Eliminar" class="text-red-600 bg-red-50 hover:bg-red-100 px-2 py-1 rounded"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-gray-400">No hay egresos en este periodo.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4 no-print">{{ $egresos->links() }}</div>
    </div>
@endsection
