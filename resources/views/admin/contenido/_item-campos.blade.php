<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="etiqueta" for="titulo">{{ $config['titulo'] }} <span class="text-red-500">*</span></label>
        <input type="text" id="titulo" name="titulo" value="{{ old('titulo', $item->titulo ?? '') }}" class="campo" maxlength="150" required>
    </div>
    <div>
        <label class="etiqueta" for="orden">Orden (menor aparece primero)</label>
        <input type="number" id="orden" name="orden" value="{{ old('orden', $item->orden ?? 0) }}" min="0" class="campo">
    </div>
    @if($config['descripcion'])
        <div class="md:col-span-2">
            <label class="etiqueta" for="descripcion">{{ $config['descripcion'] }}</label>
            <textarea id="descripcion" name="descripcion" rows="3" maxlength="500" class="campo">{{ old('descripcion', $item->descripcion ?? '') }}</textarea>
        </div>
    @endif
    @if($config['detalle'])
        <div class="md:col-span-2">
            <label class="etiqueta" for="detalle">{{ $config['detalle'] }}</label>
            <input type="text" id="detalle" name="detalle" value="{{ old('detalle', $item->detalle ?? '') }}" maxlength="150" class="campo">
        </div>
    @endif
    <div class="md:col-span-2">
        <label class="etiqueta" for="imagen">Imagen</label>
        @if(! empty($item?->imagen))
            <img src="{{ $item->imagen_url }}" alt="" class="h-32 rounded-lg border border-gray-200 object-cover mb-2">
        @endif
        <input type="file" id="imagen" name="imagen" accept="image/*" class="campo">
        <p class="text-gray-400 text-xs mt-1">JPG, PNG o WEBP, máximo 8MB.</p>
    </div>
    <label class="flex items-center gap-2 text-sm text-gray-700 md:col-span-2">
        <input type="checkbox" name="activo" value="1" {{ old('activo', $item->activo ?? true) ? 'checked' : '' }}> Mostrar en la página
    </label>
</div>
