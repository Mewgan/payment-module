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
     * @param $id
     * @return \JetFire\Framework\System\Redirect|mixed|null
     */
    public function getInvoice(Auth $auth, Pdf $pdf, LogProvider $logger, $id)
    {
        /** @var Payment $payment */
        $payment = Payment::findOneById($id);
        if(!is_null($payment) && isset($this->app->data['app']['Payment']['invoice_callbacks'][$payment->getType()])) {
            $callback = explode('@', $this->app->data['app']['Payment']['invoice_callbacks'][$payment->getType()]);
            if(isset($callback[1])) {
                $response = $this->callMethod($callback[0], $callback[1], ['auth' => $auth, 'pdf' => $pdf, 'logger' => $logger, 'payment' => $payment]);
                if(!is_null($response)) return $response;
            }
        }
        return $this->redirect('public.page', ['_locale' => $this->app->data['_locale']]);
    }

    /**
     * @param Auth $auth
     * @param Pdf $pdf
     * @param LogProvider $logger
     * @param Payment $payment
     * @return mixed
     */
    public function getSubscriptionInvoice(Auth $auth, Pdf $pdf, LogProvider $logger, Payment $payment)
    {
        if (!is_null($payment->getWebsite()) && $this->isWebsiteOwner($auth, $payment->getWebsite())) {
            if (!is_null($payment)) {
                $society = Payment::repo()->getSocietyDetail($payment->getWebsite()->getId());
                if (!is_null($society)) {
                    return $this->renderInvoice($pdf, $logger, $payment->getReference(), 'Invoice/subscription', [
                        'payment' => $payment,
                        'invoice_address' => $payment->getInvoiceAddress(),
                        'society' => $society,
                    ]);
                }
            }
        }
        return null;
    }


    /**
     * @param Pdf $pdf
     * @param LogProvider $logger
     * @param $reference
     * @param $layout
     * @param array $data
     * @return bool
     */
    private function renderInvoice(Pdf $pdf, LogProvider $logger, $reference, $layout, $data = [])
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
        return null;
    }
}