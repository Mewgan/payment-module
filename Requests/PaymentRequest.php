<?php

namespace Jet\Modules\Payment\Requests;

use JetFire\Framework\System\Request;

/**
 * Class PaymentRequest
 * @package Jet\Modules\Payment\Requests
 */
class PaymentRequest extends Request
{

    /**
     * @var array
     */
    public static $messages = [
        'required' => 'Le champ ":field" doit être rempli',
    ];


    /**
     * @return array
     */
    public function rules()
    {
        return [
            'stripeToken' => 'required',
            'total|month' => 'required|numeric',
        ];
    }

}