<?php

return [

    'roles' => [
        'developer' => 'Developer (BLStudio)',
        'admin'     => 'Administrador',
        'cajero'    => 'Cajero',
        'kitchen'   => 'Cocina',
        'delivery'  => 'Repartidor',
        'customer'  => 'Cliente',
    ],

    'editable_roles' => ['cajero'],

    'groups' => [
        'orders' => [
            'label' => 'Pedidos',
            'permissions' => [
                'orders.view'          => 'Ver pedidos',
                'orders.change_status' => 'Cambiar estado de pedidos',
                'orders.edit'          => 'Editar pedidos',
                'orders.delete'        => 'Eliminar pedidos',
                'orders.print'         => 'Imprimir pedidos',
            ],
        ],
        'products' => [
            'label' => 'Productos',
            'permissions' => [
                'products.view'   => 'Ver productos',
                'products.create' => 'Crear productos',
                'products.edit'   => 'Editar productos',
                'products.delete' => 'Eliminar productos',
            ],
        ],
        'categories' => [
            'label' => 'Categorías',
            'permissions' => [
                'categories.view'   => 'Ver categorías',
                'categories.manage' => 'Gestionar categorías',
            ],
        ],
        'kitchen' => [
            'label' => 'Cocina',
            'permissions' => [
                'kitchen.view' => 'Ver vista de cocina',
            ],
        ],
        'coupons' => [
            'label' => 'Cupones',
            'permissions' => [
                'coupons.view'   => 'Ver cupones',
                'coupons.manage' => 'Gestionar cupones',
            ],
        ],
        'loyalty' => [
            'label' => 'Fidelización',
            'permissions' => [
                'loyalty.view'                => 'Ver fidelización',
                'loyalty.approve_redemptions' => 'Aprobar canjes',
                'loyalty.manage_config'       => 'Configurar fidelización',
            ],
        ],
        'reviews' => [
            'label' => 'Reseñas',
            'permissions' => [
                'reviews.view'     => 'Ver reseñas',
                'reviews.moderate' => 'Moderar reseñas',
            ],
        ],
        'turnos' => [
            'label' => 'Turnos',
            'permissions' => [
                'turnos.view'   => 'Ver turnos',
                'turnos.manage' => 'Gestionar turnos',
            ],
        ],
        'settings' => [
            'label' => 'Configuración',
            'permissions' => [
                'settings.view' => 'Ver configuración',
                'settings.edit' => 'Editar configuración',
            ],
        ],
        'users' => [
            'label' => 'Usuarios',
            'permissions' => [
                'users.view'   => 'Ver usuarios',
                'users.manage' => 'Gestionar usuarios',
            ],
        ],
    ],

    'defaults' => [
        'developer' => '*',
        'admin'     => '*',
        'cajero' => [
            'orders.view',
            'orders.change_status',
            'orders.print',
            'products.view',
        ],
        'kitchen' => [
            'kitchen.view',
        ],
        'delivery' => [],
        'customer' => [],
    ],

];
