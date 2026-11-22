data-visor
@if($producto->imagen) data-visor-imagen="{{ $producto->imagen_url }}" @endif
data-visor-titulo="{{ $producto->nombre }}"
data-visor-precio="S/ {{ number_format($producto->precio_venta, 2) }}"
@if($producto->incluye) data-visor-incluye="{{ json_encode($producto->incluye) }}" @endif
data-visor-texto="{{ $producto->descripcion }}"
