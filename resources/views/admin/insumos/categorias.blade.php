@extends('layouts.panel', ['nav' => 'admin.nav'])

@section('titulo', 'Categorías de Insumo')
@section('icono', 'fa-tags')
@section('subtitulo', 'Ej: Carnes, Verduras, Abarrotes, Bebidas')
@section('ancho', 'max-w-4xl')

@section('contenido')
    <form method="POST" action="{{ route('admin.insumos.categorias.store') }}" class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm mb-6 flex flex-col sm:flex-row gap-4 sm:items-end">
        @csrf
        <div class="flex-1">
            <label class="etiqueta">Nueva categoría <span class="text-red-500">*</span></label>
            <input type="text" name="nombre" value="{{ old('nombre') }}" class="campo" required>
        </div>
        <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2.5 rounded-lg font-semibold transition text-sm">
            <i class="fas fa-save mr-2"></i> Guardar
        </button>
    </form>

    <div class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-gray-500">
                    <th class="pb-3 font-semibold">Categoría</th>
                    <th class="pb-3 font-semibold text-center">Insumos</th>
                    <th class="pb-3 font-semibold text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categorias as $categoria)
                    <tr class="border-b border-gray-100">
                        <td class="py-3 text-black font-medium">{{ $categoria->nombre }}</td>
                        <td class="py-3 text-center text-gray-600">{{ $categoria->insumos_count }}</td>
                        <td class="py-3 text-center">
                            <form method="POST" action="{{ route('admin.insumos.categorias.destroy', $categoria->id) }}" onsubmit="return confirm('¿Eliminar esta categoría?')" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 bg-red-50 hover:bg-red-100 px-2 py-1 rounded"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-8 text-center text-gray-400">Aún no hay categorías.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
