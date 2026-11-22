{{-- Lo que caja debe enterarse, aunque esté en otra página: pedidos pendientes y adicionales sin imprimir.
     partials/auto-actualizar avisa los nuevos y resalta la fila del pedido (data-aviso-pedido). --}}
<div data-auto="avisos" hidden>
    @foreach($idsPendientes as $idPendiente)<span data-aviso-id="p{{ $idPendiente }}" data-aviso-tipo="pedido" data-aviso-pedido="{{ $idPendiente }}"></span>@endforeach
    @foreach($adicionales as $idItem => $idPedido)<span data-aviso-id="a{{ $idItem }}" data-aviso-tipo="adicional" data-aviso-pedido="{{ $idPedido }}"></span>@endforeach
</div>
