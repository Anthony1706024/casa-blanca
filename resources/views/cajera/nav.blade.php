@include('layouts.staff-nav', [
    'rol' => 'Caja',
    'secciones' => [
        'Menú Principal' => [
            ['cajera.dashboard', 'fa-home', 'Dashboard'],
        ],
        'Caja' => [
            ['cajera.pedidos.create', 'fa-plus-circle', 'Nuevo Pedido'],
            ['cajera.pedidos.index', 'fa-cash-register', 'Pedidos por Cobrar'],
            ['cajera.recibos.index', 'fa-receipt', 'Recibos Emitidos'],
            ['impresiones.index', 'fa-print', 'Impresiones'],
        ],
    ],
])
