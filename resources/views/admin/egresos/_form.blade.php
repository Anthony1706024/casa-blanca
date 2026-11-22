{{-- Campos de un egreso. $egreso (o null), $categorias, $metodos --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <div>
        <label class="etiqueta">Fecha *</label>
        <input type="date" name="fecha" required max="{{ now()->toDateString() }}" class="campo"
               value="{{ old('fecha', $egreso?->fecha?->toDateString() ?? now()->toDateString()) }}">
    </div>
    <div>
        <label class="etiqueta">Tipo de egreso *</label>
        <select name="categoria_egreso_id" required class="campo">
            <option value="">Elige...</option>
            @foreach($categorias as $categoria)
                <option value="{{ $categoria->id }}" @selected(old('categoria_egreso_id', $egreso?->categoria_egreso_id) == $categoria->id)>{{ $categoria->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="etiqueta">Monto (S/) *</label>
        <input type="number" name="monto" required step="0.01" min="0.01" class="campo" placeholder="0.00"
               value="{{ old('monto', $egreso?->monto) }}">
    </div>
    <div class="sm:col-span-2 lg:col-span-3">
        <label class="etiqueta">Descripción *</label>
        <input type="text" name="descripcion" required maxlength="255" class="campo" placeholder="Ej.: 10 kg de pescado, 2 balones de gas, sueldo de la semana..."
               value="{{ old('descripcion', $egreso?->descripcion) }}">
    </div>
    <div>
        <label class="etiqueta">Proveedor o persona</label>
        <input type="text" name="proveedor" maxlength="120" class="campo" placeholder="Ej.: Backus, mercado, Juan"
               value="{{ old('proveedor', $egreso?->proveedor) }}">
    </div>
    <div>
        <label class="etiqueta">Pagado con</label>
        <select name="metodo" class="campo">
            <option value="">—</option>
            @foreach($metodos as $clave => $nombre)
                <option value="{{ $clave }}" @selected(old('metodo', $egreso?->metodo) === $clave)>{{ $nombre }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="etiqueta">N.º de boleta o factura</label>
        <input type="text" name="comprobante" maxlength="60" class="campo" placeholder="Opcional"
               value="{{ old('comprobante', $egreso?->comprobante) }}">
    </div>
</div>
