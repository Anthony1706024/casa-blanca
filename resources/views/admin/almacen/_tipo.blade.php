@php
    $tipoActual = old('tipo', $producto->tipo ?? \App\Models\Producto::DIRECTO);
    // Una casilla desmarcada no se envía: tras un error de validación, su ausencia significa "no visible"
    $recetaActual = old('receta')
        ? collect(old('receta'))->map(fn ($linea) => [...$linea, 'visible' => ! empty($linea['visible'])])->values()->all()
        : (isset($producto)
            ? $producto->insumos->map(fn ($insumo) => ['insumo_id' => $insumo->id, 'cantidad' => \App\Models\Insumo::formatear($insumo->pivot->cantidad), 'visible' => (bool) $insumo->pivot->visible_cliente])->values()->all()
            : []);
@endphp

<!-- Tipo de producto -->
<div class="md:col-span-2">
    <label class="block text-gray-700 mb-2 font-semibold text-sm md:text-base">
        Tipo de producto <span class="text-red-500">*</span>
    </label>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <label class="flex items-start gap-3 p-3 border border-gray-300 rounded-lg cursor-pointer">
            <input type="radio" name="tipo" value="directo" class="mt-1" {{ $tipoActual === 'directo' ? 'checked' : '' }}>
            <span class="text-sm"><strong>Con stock propio</strong><br><span class="text-gray-500">Se vende y se descuenta de su propio stock.</span></span>
        </label>
        <label class="flex items-start gap-3 p-3 border border-gray-300 rounded-lg cursor-pointer">
            <input type="radio" name="tipo" value="plato" class="mt-1" {{ $tipoActual === 'plato' ? 'checked' : '' }}>
            <span class="text-sm"><strong>Plato (con receta)</strong><br><span class="text-gray-500">Cada venta consume insumos. También sirve para gaseosas registradas como insumo.</span></span>
        </label>
    </div>
</div>

<!-- Receta -->
<div id="bloqueReceta" class="md:col-span-2 hidden">
    <label class="block text-gray-700 mb-2 font-semibold text-sm md:text-base">
        Receta: insumos que consume 1 unidad <span class="text-red-500">*</span>
    </label>
    <p class="text-gray-500 text-xs mb-2">
        La cantidad va en la unidad del insumo (por ejemplo, 200 si el arroz está en gramos). El cliente ve «Arroz» sin gramos; si el insumo va en presas o unidades, ve «1 presa de Pato». Desmarca «El cliente lo ve» en cosas como sal o aceite: se siguen descontando del stock.
    </p>

    @foreach($errors->get('receta*') as $mensajes)
        @foreach($mensajes as $mensaje)
            <p class="text-red-500 text-sm mb-1"><i class="fas fa-exclamation-circle mr-1"></i> {{ $mensaje }}</p>
        @endforeach
    @endforeach

    @if($insumos->isEmpty())
        <p class="text-sm text-orange-700 bg-orange-50 border border-orange-200 rounded-lg px-4 py-3">
            Aún no hay insumos. <a href="{{ route('admin.insumos.create') }}" class="font-semibold underline">Registra uno primero</a>.
        </p>
    @else
        <div id="filasReceta" class="space-y-2"></div>
        <button type="button" id="agregarInsumo" class="mt-3 text-sm font-semibold text-orange-600 hover:text-orange-700">
            <i class="fas fa-plus-circle mr-1"></i> Agregar insumo
        </button>
    @endif
</div>

<template id="filaReceta">
    <div class="flex flex-wrap gap-2 items-center">
        <select data-campo="insumo_id" class="flex-1 px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm" required>
            <option value="">Seleccionar insumo</option>
            @foreach($insumos as $insumo)
                <option value="{{ $insumo->id }}">{{ $insumo->nombre }} ({{ $insumo->unidad }})</option>
            @endforeach
        </select>
        <input type="number" data-campo="cantidad" step="0.001" min="0.001" placeholder="Cantidad" class="w-28 px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm" required>
        <label class="flex items-center gap-1 text-xs text-gray-700 whitespace-nowrap">
            <input type="checkbox" data-visible value="1"> El cliente lo ve
        </label>
        <button type="button" data-quitar class="text-red-600 bg-red-50 hover:bg-red-100 px-3 py-2 rounded-lg"><i class="fas fa-trash"></i></button>
    </div>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const bloque = document.getElementById('bloqueReceta');
        const campoStock = document.getElementById('campoStock');
        const filas = document.getElementById('filasReceta');
        const plantilla = document.getElementById('filaReceta');
        let indice = 0;

        function agregarFila(valores = {}) {
            const fila = plantilla.content.cloneNode(true).firstElementChild;
            fila.querySelectorAll('[data-campo]').forEach(function (campo) {
                campo.name = `receta[${indice}][${campo.dataset.campo}]`;
                campo.value = valores[campo.dataset.campo] ?? '';
            });
            const visible = fila.querySelector('[data-visible]');
            visible.name = `receta[${indice}][visible]`;
            visible.checked = valores.visible ?? true;
            fila.querySelector('[data-quitar]').addEventListener('click', () => fila.remove());
            filas.appendChild(fila);
            indice++;
        }

        // Los campos desactivados no se envían ni se validan en el navegador
        function aplicarTipo() {
            const esPlato = document.querySelector('input[name="tipo"]:checked').value === 'plato';
            bloque.classList.toggle('hidden', !esPlato);
            campoStock.classList.toggle('hidden', esPlato);
            campoStock.querySelector('input').disabled = esPlato;
            bloque.querySelectorAll('select, input').forEach(campo => campo.disabled = !esPlato);
            if (esPlato && filas && !filas.children.length) {
                agregarFila();
            }
        }

        if (filas) {
            @json(array_values($recetaActual)).forEach(agregarFila);
            document.getElementById('agregarInsumo').addEventListener('click', () => agregarFila());
        }

        document.querySelectorAll('input[name="tipo"]').forEach(radio => radio.addEventListener('change', aplicarTipo));
        aplicarTipo();
    });
</script>
