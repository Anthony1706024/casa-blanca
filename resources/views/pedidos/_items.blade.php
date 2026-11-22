{{-- Platos del pedido con su cocina, si ya salieron a cocina y, en un pedido pendiente, botones para quitar.
     Lo usan caja y mozo: $prefijo = 'cajera' | 'mozo' (nombre de la ruta pedidos.items.quitar). --}}
<table class="w-full text-sm border-t border-gray-200">
    @foreach($pedido->items as $item)
        <tr class="border-b border-gray-100 {{ $item->esAdicional() ? 'bg-amber-50/60' : '' }}">
            <td class="py-2 pr-2 text-gray-600 align-top">{{ $item->cantidad }} ×</td>
            <td class="py-2 text-black">
                {{ $item->producto_nombre }}
                @if($item->esAdicional())
                    <span class="ml-1 text-[10px] font-bold uppercase tracking-wide bg-amber-200 text-amber-900 rounded px-1.5 py-0.5">Adicional</span>
                @endif
                <div class="text-xs text-gray-400">
                    {{ $item->producto->cocina_nombre ?? '' }}
                    @if($item->por_enviar > 0)
                        · <span class="text-amber-600 font-semibold">{{ $item->por_enviar === (int) $item->cantidad ? 'Por enviar a cocina' : $item->por_enviar.' por enviar a cocina' }}</span>
                    @else
                        · <span class="text-green-600"><i class="fas fa-check"></i> En cocina</span>
                    @endif
                </div>
            </td>
            <td class="py-2 text-right whitespace-nowrap align-top">S/ {{ number_format($item->subtotal, 2) }}</td>
            @if($pedido->estado === \App\Models\Pedido::PENDIENTE)
                @php $avisoCocina = $item->cantidad_enviada > 0 ? ' Ya salió a cocina: avisa que no lo preparen.' : ''; @endphp
                <td class="py-2 pl-2 align-top whitespace-nowrap text-right">
                    @if($item->cantidad > 1)
                        <form method="POST" action="{{ route($prefijo.'.pedidos.items.quitar', [$pedido->id, $item->id]) }}" class="inline"
                              onsubmit="return confirm({{ \Illuminate\Support\Js::from('¿Quitar 1 × '.$item->producto_nombre.'?'.$avisoCocina) }})">
                            @csrf
                            <input type="hidden" name="cantidad" value="1">
                            <button type="submit" title="Quitar uno" aria-label="Quitar uno de {{ $item->producto_nombre }}" class="text-gray-600 bg-gray-100 hover:bg-gray-200 w-7 h-7 rounded font-bold">−</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route($prefijo.'.pedidos.items.quitar', [$pedido->id, $item->id]) }}" class="inline"
                          onsubmit="return confirm({{ \Illuminate\Support\Js::from('¿Quitar '.$item->cantidad.' × '.$item->producto_nombre.' del pedido?'.$avisoCocina) }})">
                        @csrf
                        <button type="submit" title="Quitar del pedido" aria-label="Quitar {{ $item->producto_nombre }} del pedido" class="text-red-600 bg-red-50 hover:bg-red-100 w-7 h-7 rounded"><i class="fas fa-trash-alt text-xs"></i></button>
                    </form>
                </td>
            @endif
        </tr>
    @endforeach
</table>
