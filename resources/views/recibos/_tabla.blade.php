<div class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm overflow-x-auto">
    <table class="w-full text-left text-sm">
        <thead>
            <tr class="border-b border-gray-200 text-gray-500">
                <th class="pb-3 font-semibold">Recibo</th>
                <th class="pb-3 font-semibold">Fecha</th>
                <th class="pb-3 font-semibold">DNI</th>
                <th class="pb-3 font-semibold">Transacción</th>
                <th class="pb-3 font-semibold text-right">Total</th>
                <th class="pb-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($recibos as $recibo)
                <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                    <td class="py-3 text-orange-600 font-mono font-medium">{{ $recibo->numero }}</td>
                    <td class="py-3 text-gray-600">{{ $recibo->created_at->format('d/m/Y H:i') }}</td>
                    <td class="py-3 text-black">{{ $recibo->pedido->dni ?? '—' }}</td>
                    <td class="py-3 text-gray-600">
                        @foreach($recibo->pagos as $pago)
                            <div>{{ $pago->metodo_nombre }} S/ {{ number_format($pago->monto, 2) }}@if($pago->numero_operacion) · Op. {{ $pago->numero_operacion }}@endif</div>
                        @endforeach
                    </td>
                    <td class="py-3 text-right text-black font-semibold">S/ {{ number_format($recibo->total, 2) }}</td>
                    <td class="py-3 text-right whitespace-nowrap">
                        {{-- Ver: se abre en otra ventana, esta lista no se pierde --}}
                        <a href="{{ route('recibos.show', $recibo->id) }}" target="recibo-{{ $recibo->id }}" title="Ver recibo"
                           onclick="window.open(this.href, this.target, 'width=480,height=860'); return false;"
                           class="inline-block text-blue-600 bg-blue-50 hover:bg-blue-100 px-2 py-1 rounded"><i class="fas fa-eye"></i></a>
                        <form method="POST" action="{{ route('recibos.destroy', $recibo->id) }}" class="inline"
                              onsubmit="return confirm({{ \Illuminate\Support\Js::from('¿Borrar el recibo '.$recibo->numero.' y su pedido '.$recibo->pedido->numero.'? Se elimina de la lista y de la base de datos y no se puede deshacer. El stock no se devuelve.') }})">
                            @csrf @method('DELETE')
                            <button type="submit" title="Borrar recibo y pedido" aria-label="Borrar {{ $recibo->numero }}" class="text-red-600 bg-red-50 hover:bg-red-100 px-2 py-1 rounded"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-8 text-center text-gray-400">No hay recibos.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4">{{ $recibos->links() }}</div>
</div>
