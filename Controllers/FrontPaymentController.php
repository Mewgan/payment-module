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
     * @param Pdf $pdf
     * @param LogProvider $logger 
     * @param $website
     * @param $id
     * @return mixed
     */
    public function getSubscriptionInvoice(Auth $auth, Pdf $pdf, LogProvider $logger, $website, $id)
    {
        if ($this->isWebsiteOwner($auth, $website)) {
            /** @var Payment $payment */
            $payment = Payment::findOneBy(['id' => $id, 'website' => $website]);
            if (!is_null($payment)) {
                $society = Payment::repo()->getSocietyDetail($website);
                if (!is_null($society)) {
                    $this->getInvoice($pdf, $logger, 'Invoice/subscription', [
                        'payment' => $payment,
                        'society' => $society,
                    ]);
                }
            }
        }
        return $this->redirect('public.page', ['_locale' => $this->app->data['_locale']]);
    }


    /**
     * @param Pdf $pdf
     * @param LogProvider $logger
     * @param $reference
     * @param $layout
     * @param array $data
     * @return bool
     */
    private function getInvoice(Pdf $pdf, LogProvider $logger, $reference, $layout, $data = [])
    {
        $log = $logger->getLogger('payment');
        try {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $pdf->binary = 'C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe';
            } else {
                $pdf->binary = 'xvfb-run -- /usr/bin/wkhtmltopdf';
            }
            Stripe::setApiKey($this->app->data['setting']['payment']['stripe']['secret_key']);
            $charge = Charge::retrieve($reference);
            $content = $this->render($layout, array_merge(['charge' => $charge], $data));

            $pdf->addPage($content);
            $response = $pdf->send();
            if (!$response) $log->addError($pdf->getError());

            return $response;
        } catch (\Exception $e) {
            $log->addError($e->getMessage());
        }
        return true;
    }
}