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
                    <td class="py-3 text-black">{{ $recibo->pedido->dni }}</td>
                    <td class="py-3 text-gray-600">
                        @foreach($recibo->pagos as $pago)
                            <div>{{ $pago->metodo_nombre }} S/ {{ number_format($pago->monto, 2) }}@if($pago->numero_operacion) · Op. {{ $pago->numero_operacion }}@endif</div>
                        @endforeach
                    </td>
                    <td class="py-3 text-right text-black font-semibold">S/ {{ number_format($recibo->total, 2) }}</td>
                    <td class="py-3 text-right">
                        <a href="{{ route('recibos.show', $recibo->id) }}" class="text-blue-600 bg-blue-50 hover:bg-blue-100 px-2 py-1 rounded"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-8 text-center text-gray-400">No hay recibos.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4">{{ $recibos->links() }}</div>
</div>
