@if($categorias->isEmpty())
    <div class="bg-orange-50 border border-orange-200 text-orange-700 px-4 py-3 rounded-lg mb-6 text-sm">
        Primero crea una <a href="{{ route('admin.insumos.categorias.index') }}" class="font-semibold underline">categoría de insumo</a>.
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
    <div class="md:col-span-2">
        <label class="etiqueta">Nombre <span class="text-red-500">*</span></label>
        <input type="text" name="nombre" value="{{ old('nombre', $insumo->nombre ?? '') }}" class="campo" placeholder="Ej: Arroz, Pescado, Gaseosa 500ml" required>
    </div>

    <div>
        <label class="etiqueta">Categoría <span class="text-red-500">*</span></label>
        <select name="categoria_insumo_id" class="campo" required>
            <option value="">Seleccionar categoría</option>
            @foreach($categorias as $categoria)
                <option value="{{ $categoria->id }}" {{ old('categoria_insumo_id', $insumo->categoria_insumo_id ?? '') == $categoria->id ? 'selected' : '' }}>{{ $categoria->nombre }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="etiqueta">Unidad de medida <span class="text-red-500">*</span></label>
        <select name="unidad" class="campo" required>
            @foreach($unidades as $unidad)
                <option value="{{ $unidad }}" {{ old('unidad', $insumo->unidad ?? '') === $unidad ? 'selected' : '' }}>{{ $unidad }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="etiqueta">Stock <span class="text-red-500">*</span></label>
        <input type="number" name="stock" value="{{ old('stock', isset($insumo) ? \App\Models\Insumo::formatear($insumo->stock) : 0) }}" step="0.001" min="0" class="campo" required>
    </div>

    <div>
        <label class="etiqueta">Stock mínimo (alerta) <span class="text-red-500">*</span></label>
        <input type="number" name="stock_minimo" value="{{ old('stock_minimo', isset($insumo) ? \App\Models\Insumo::formatear($insumo->stock_minimo) : 0) }}" step="0.001" min="0" class="campo" required>
    </div>
</div>

<div class="flex flex-col sm:flex-row justify-end gap-3 mt-8">
    <a href="{{ route('admin.insumos.index') }}" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-semibold transition text-center text-sm border border-gray-200">Cancelar</a>
    <button type="submit" class="px-8 py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-semibold transition text-sm shadow-sm">
        <i class="fas fa-save mr-2"></i> Guardar Insumo
    </button>
</div>
