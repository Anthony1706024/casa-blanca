@include('layouts.staff-nav', [
    'rol' => 'Mozo',
    'secciones' => [
        'Menú Principal' => [
            ['mozo.dashboard', 'fa-home', 'Dashboard'],
        ],
        'Pedidos' => [
            ['mozo.pedidos.create', 'fa-plus-circle', 'Registrar Pedido'],
            ['mozo.pedidos.index', 'fa-clipboard-list', 'Ver Pedidos'],
        ],
    ],
])
