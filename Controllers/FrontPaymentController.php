<?php

namespace Jet\Modules\Payment\Controllers;

use Jet\Modules\Payment\Models\Payment;
use Jet\Services\Auth;
use Jet\Services\WebsiteHelper;
use JetFire\Framework\Providers\LogProvider;
use JetFire\Framework\System\Controller;
use mikehaertl\wkhtmlto\Pdf;
use Stripe\Charge;
use Stripe\Stripe;

/**
 * Class FrontPaymentController
 * @package Jet\Modules\Payment\Controllers
 */
class FrontPaymentController extends Controller
{
    use WebsiteHelper;

    /**
     * @param Auth $auth
     * @param LogProvider $logger
     * @param Pdf $pdf
     * @param $website
     * @param $id
     * @return mixed
     */
    public function getInvoice(Auth $auth, LogProvider $logger, Pdf $pdf, $website, $id)
    {
        if ($this->isWebsiteOwner($auth, $website)) {
            /** @var Payment $payment */
            $payment = Payment::findOneBy(['id' => $id, 'website' => $website]);
            if (!is_null($payment)) {
                $log = $logger->getLogger('payment');
                $setting = $this->app->data['setting'];
                $society = Payment::repo()->getSocietyDetail($website);
                if (!is_null($society)) {
                    try {
                        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                            $pdf->binary = 'C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe';
                        }
                        Stripe::setApiKey($this->app->data['setting']['payment']['stripe']['secret_key']);
                        $charge = Charge::retrieve($payment->getReference());
                        $content = $this->render('Invoice/layout', [
                            'charge' => $charge,
                            'payment' => $payment,
                            'society' => $society,
                            'setting' => $setting
                        ]);

                        $pdf->addPage($content);
                        $response = $pdf->send();
                        if (!$response) $log->addError($pdf->getError());

                        return $response;
                    } catch (\Exception $e) {
                        $log->addError($e->getMessage());
                    }
                }
            }
        }
        return $this->redirect('public.page');
    }

}