@php($colores = ['pendiente' => 'bg-orange-100 text-orange-700', 'pagado' => 'bg-green-100 text-green-700', 'anulado' => 'bg-gray-200 text-gray-600'])
<span class="px-3 py-1 rounded-full text-xs font-semibold {{ $colores[$estado] ?? '' }}">{{ ucfirst($estado) }}</span>
