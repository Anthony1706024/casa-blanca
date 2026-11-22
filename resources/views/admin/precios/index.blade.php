@extends('layouts.panel', ['nav' => 'admin.nav'])

@section('titulo', 'Precios y Detalle')
@section('icono', 'fa-tags')
@section('subtitulo', 'Cambiar un precio solo afecta a los pedidos nuevos: las ventas anteriores conservan su precio')

@section('contenido')
    @include('admin.precios._tabs')

    <form method="GET" action="{{ route('admin.precios.index') }}" class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
        <div>
            <label class="etiqueta">Buscar</label>
            <input type="text" name="search" value="{{ request('search') }}" class="campo" placeholder="Nombre del producto">
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
        <div>
            <label class="etiqueta">Cocina</label>
            <select name="cocina" class="campo">
                <option value="">Todas</option>
                @foreach(\App\Models\Producto::COCINAS as $valor => $texto)
                    <option value="{{ $valor }}" {{ request('cocina') === $valor ? 'selected' : '' }}>{{ $texto }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2.5 rounded-lg font-semibold transition text-sm">
            <i class="fas fa-search mr-2"></i> Buscar
        </button>
    </form>

    <form method="POST" action="{{ route('admin.precios.actualizar') }}" class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm">
        @csrf @method('PUT')

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-gray-500">
                        <th class="pb-3 font-semibold">Producto</th>
                        <th class="pb-3 font-semibold hidden sm:table-cell">Categoría</th>
                        <th class="pb-3 font-semibold">Qué trae (se ve al hacer clic en la carta)</th>
                        <th class="pb-3 font-semibold" title="En qué ticket de cocina sale">Cocina</th>
                        <th class="pb-3 font-semibold text-center" title="Se muestra en la página de inicio">Destacado en la web</th>
                        <th class="pb-3 font-semibold text-right">Precio de venta (S/)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $producto)
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                            <td class="py-2 text-black font-medium">{{ $producto->nombre }}</td>
                            <td class="py-2 text-gray-600 hidden sm:table-cell">{{ $producto->categoria->nombre ?? '-' }}</td>
                            <td class="py-2 pr-4">
                                <input type="text" name="descripciones[{{ $producto->id }}]" value="{{ old("descripciones.$producto->id", $producto->descripcion === $producto->nombre ? '' : $producto->descripcion) }}" maxlength="1000"
                                       placeholder="Ej: Viene con papa y menestra" aria-label="Qué trae {{ $producto->nombre }}" class="w-full min-w-[14rem] px-3 py-1.5 bg-gray-50 border border-gray-300 rounded-lg text-sm">
                            </td>
                            <td class="py-2 pr-4">
                                <select name="cocinas[{{ $producto->id }}]" aria-label="Cocina de {{ $producto->nombre }}" class="px-2 py-1.5 bg-gray-50 border border-gray-300 rounded-lg text-sm">
                                    @foreach(\App\Models\Producto::COCINAS as $valor => $texto)
                                        <option value="{{ $valor }}" {{ old("cocinas.$producto->id", $producto->cocina) === $valor ? 'selected' : '' }}>{{ $texto }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="py-2 text-center">
                                <input type="checkbox" name="destacados[{{ $producto->id }}]" value="1" aria-label="Destacar {{ $producto->nombre }}" {{ old("destacados.$producto->id", $producto->destacado) ? 'checked' : '' }}>
                            </td>
                            <td class="py-2 text-right">
                                <input type="number" name="precios[{{ $producto->id }}]" value="{{ old("precios.$producto->id", $producto->precio_venta) }}" step="0.01" min="0"
                                       aria-label="Precio de {{ $producto->nombre }}" class="w-28 px-3 py-1.5 bg-gray-50 border border-gray-300 rounded-lg text-right text-sm">
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"class="py-8 text-center text-gray-400">No se encontraron productos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex justify-end mt-6">
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-8 py-3 rounded-lg font-semibold transition text-sm shadow-sm">
                <i class="fas fa-save mr-2"></i> Guardar esta página
            </button>
        </div>
    </form>

    <div class="mt-4">{{ $productos->links() }}</div>
@endsection
