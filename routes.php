<?php

return [

    '/module/payment/*' => [
        'use' => 'AdminPaymentController@{method}',
        'ajax' => true
    ],
];