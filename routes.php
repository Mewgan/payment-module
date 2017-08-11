<?php

return [

    /* Front */
    '/module/payment/get-invoice/:id' => [
        'use' => 'FrontPaymentController@getInvoice',
        'arguments' => ['id' => '[0-9]*'],
    ],

    /* prod */
    '{subdomain}.{host}/module/payment/*' => [
        'use' => 'AdminPaymentController@{method}',
        'ajax' => true,
        'subdomain' => 'admin',
        'middleware' => 'admin'
    ],

    /* dev */
    '/admin/module/payment/*' => [
        'use' => 'AdminPaymentController@{method}',
        'ajax' => true,
        'middleware' => 'admin'
    ]
];