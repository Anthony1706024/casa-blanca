<!-- Datos del pedido que registra el personal (mozo o caja). Solo la mesa es obligatoria. -->
<div class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div>
        <label class="etiqueta" for="mesa">Mesa <span class="text-red-500">*</span></label>
        <input type="text" id="mesa" name="mesa" value="{{ old('mesa') }}" class="campo" maxlength="20" placeholder="Ej: 12" required autofocus>
    </div>
    <div>
        <label class="etiqueta" for="dni">DNI <span class="text-gray-400 font-normal">(opcional)</span></label>
        <input type="text" id="dni" name="dni" value="{{ old('dni') }}" class="campo" inputmode="numeric" maxlength="8" pattern="[0-9]{8}" title="8 dígitos, o déjalo vacío">
    </div>
    <div>
        <label class="etiqueta" for="cliente_nombre">Nombre <span class="text-gray-400 font-normal">(opcional)</span></label>
        <input type="text" id="cliente_nombre" name="cliente_nombre" value="{{ old('cliente_nombre') }}" class="campo" maxlength="150">
    </div>
    <div>
        <label class="etiqueta" for="observacion">Observación</label>
        <input type="text" id="observacion" name="observacion" value="{{ old('observacion') }}" class="campo" maxlength="255" placeholder="Ej: sin ají">
    </div>
</div>
