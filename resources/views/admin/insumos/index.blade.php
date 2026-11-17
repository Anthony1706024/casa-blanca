@extends('layouts.panel', ['nav' => 'admin.nav'])

@section('titulo', 'Insumos')
@section('icono', 'fa-cubes')
@section('subtitulo', 'Materia prima y bebidas: de aquí se descuenta lo que consumen los platos')

@section('acciones')
    <a href="{{ route('admin.insumos.categorias.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold transition text-sm border border-gray-200">
        <i class="fas fa-tags mr-1"></i> Categorías
    </a>
    <a href="{{ route('admin.insumos.create') }}" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-semibold transition text-sm shadow-sm">
        <i class="fas fa-plus-circle mr-1"></i> Nuevo
    </a>
@endsection

@section('contenido')
    <!-- Filtros -->
    <form method="GET" action="{{ route('admin.insumos.index') }}" class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
        <div>
            <label class="etiqueta">Buscar</label>
            <input type="text" name="search" value="{{ request('search') }}" class="campo" placeholder="Nombre del insumo">
        </div>
        <div>
            <label class="etiqueta">Categoría</label>
            <select name="categoria" class="campo">
                <option value="">Todas</option>
                @foreach($categorias as $categoria)
                    <option value="{{ $categoria->id }}" {{ request('categoria') == $categoria->id ? 'selected' : '' }}>{{ $categoria->nombre }}</option>
                @endforeach
            </select>
        </div>
        <label class="flex items-center gap-2 text-sm text-gray-700 pb-3">
            <input type="checkbox" name="bajo" value="1" {{ request('bajo') ? 'checked' : '' }}> Solo stock bajo
        </label>
        <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2.5 rounded-lg font-semibold transition text-sm">
            <i class="fas fa-search mr-2"></i> Buscar
        </button>
    </form>

    <!-- Tabla -->
    <div class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-gray-500">
                    <th class="pb-3 font-semibold">Insumo</th>
                    <th class="pb-3 font-semibold hidden sm:table-cell">Categoría</th>
                    <th class="pb-3 font-semibold text-center">Stock</th>
                    <th class="pb-3 font-semibold text-center hidden md:table-cell">Mínimo</th>
                    <th class="pb-3 font-semibold">Ingreso</th>
                    <th class="pb-3 font-semibold text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($insumos as $insumo)
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                        <td class="py-3 text-black font-medium">{{ $insumo->nombre }}</td>
                        <td class="py-3 text-gray-600 hidden sm:table-cell">{{ $insumo->categoria->nombre }}</td>
                        <td class="py-3 text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $insumo->stock_bajo ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600' }}">
                                {{ \App\Models\Insumo::formatear($insumo->stock) }} {{ $insumo->unidad }}
                            </span>
                        </td>
                        <td class="py-3 text-center text-gray-500 hidden md:table-cell">{{ \App\Models\Insumo::formatear($insumo->stock_minimo) }}</td>
                        <td class="py-3">
                            <form method="POST" action="{{ route('admin.insumos.ingreso', $insumo->id) }}" class="flex gap-1">
                                @csrf
                                <input type="number" name="cantidad" step="0.001" min="0.001" required class="w-20 px-2 py-1 bg-gray-50 border border-gray-300 rounded text-sm" placeholder="0">
                                <button type="submit" title="Sumar al stock" class="text-green-600 bg-green-50 hover:bg-green-100 px-2 py-1 rounded"><i class="fas fa-plus"></i></button>
                            </form>
                        </td>
                        <td class="py-3 text-center whitespace-nowrap">
                            <a href="{{ route('admin.insumos.edit', $insumo->id) }}" class="text-blue-600 bg-blue-50 hover:bg-blue-100 px-2 py-1 rounded"><i class="fas fa-pen"></i></a>
                            <form method="POST" action="{{ route('admin.insumos.destroy', $insumo->id) }}" onsubmit="return confirm('¿Eliminar este insumo?')" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 bg-red-50 hover:bg-red-100 px-2 py-1 rounded"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-8 text-center text-gray-400">No hay insumos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $insumos->links() }}</div>
    </div>
@endsection
