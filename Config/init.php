<?php

return [

    'app' => [
        'Payment' => [
            'order' => 100,
            'hook' => [
                'top_right' => true
            ],
            'routes' => [
                [
                    'title' => 'Paiement',
                    'name'=> 'module:payment',
                ]
            ]
        ],
        'blocks' => [
            'PaymentModule' => [
                'path' => 'src/Modules/Payment/',
                'namespace' => '\\Jet\\Modules\\Payment',
                'view_dir' => 'src/Modules/Payment/Views/'
            ],
        ],
        'fixtures' => [
            'src/Modules/Payment/Fixtures/'
        ],
        'admin_libs' => [
            'js' => [
                'https://js.stripe.com/v3/'
            ]
        ]
    ]
];