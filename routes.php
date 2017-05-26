<?php

return [

    /* Front */

    '/module/payment/get-invoice/:website_id/:id' => [
        'use' => 'FrontPaymentController@getInvoice',
        'arguments' => ['website_id' => '[0-9]*', 'id' => '[0-9]*'],
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