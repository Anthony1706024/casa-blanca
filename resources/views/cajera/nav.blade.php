@include('layouts.staff-nav', [
    'rol' => 'Caja',
    'secciones' => [
        'Menú Principal' => [
            ['cajera.dashboard', 'fa-home', 'Dashboard'],
        ],
        'Caja' => [
            ['cajera.pedidos.index', 'fa-cash-register', 'Pedidos por Cobrar'],
            ['cajera.recibos.index', 'fa-receipt', 'Recibos Emitidos'],
        ],
    ],
])
