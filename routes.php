<?php

return [

    /* Front */
    '/module/payment/get-subscription-invoice/:website_id/:id' => [
        'use' => 'FrontPaymentController@getSubscriptionInvoice',
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