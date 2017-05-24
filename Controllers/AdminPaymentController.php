<?php

namespace Jet\Modules\Payment\Controllers;

use Jet\AdminBlock\Controllers\AdminController;
use Jet\Models\Account;
use Jet\Models\Website;
use Jet\Modules\Payment\Models\Payment;
use Jet\Modules\Payment\Requests\PaymentRequest;
use JetFire\Framework\Providers\LogProvider;
use JetFire\Framework\System\Mail;
use Stripe\Charge;
use Stripe\Stripe;

/**
 * Class AdminPaymentController
 * @package Jet\Modules\Payment\Controllers
 */
class AdminPaymentController extends AdminController
{

    /**
     * @return string
     */
    public function getPaymentParams()
    {
        return (isset($this->app->data['setting']['payment'])) ? $this->app->data['setting']['payment'] : '';
    }

    /**
     * @param PaymentRequest $request
     * @param Mail $mail
     * @param LogProvider $logger
     * @param $website
     * @return array|bool
     */
    public function pay(PaymentRequest $request, Mail $mail, LogProvider $logger, $website)
    {
        if ($request->method() == 'POST') {
            $response = $request->validate();
            $log = $logger->getLogger('payment');
            /** @var Website $website */
            $website = Website::findOneById($website);
            if (is_null($website)) return ['status' => 'error', 'message' => 'Impossible de trouver le site'];

            /** @var Account $account */
            $account = Account::repo()->getWebsiteAccount($website);
            if (is_null($account)) return ['status' => 'error', 'message' => 'Impossible de trouver le compte associé avec votre site'];

            if ($response === true) {
                $values = $request->values();
                try {
                    Stripe::setApiKey($this->app->data['setting']['payment']['stripe']['secret_key']);
                    $charge = Charge::create([
                        'amount' => $values['total'] * 100,
                        'currency' => $this->app->data['setting']['payment']['stripe']['currency'],
                        'description' => "Abonnement de {$values['month']} mois",
                        'source' => $values['stripeToken'],
                        'receipt_email' => $account->getEmail()
                    ]);
                    if ($charge->paid === true) {
                        $payment = [
                            'title' => "Abonnement de {$values['month']} mois",
                            'amount' => $values['total'],
                            'reference' => $charge->id,
                            'website' => $website
                        ];
                        $interval = new \DateInterval('P' . $values['month'] . 'M');
                        $new_expiration_date = $website->getExpirationDate()->add($interval);
                        if (Payment::create($payment) && Website::where('id', $website->getId())->set(['expiration_date' => $new_expiration_date])) {
                            $setting = $this->app->data['setting'];
                            $content = $this->render('Mail/payment_accepted', [
                                'values' => $values,
                                'account' => $account,
                                'url' => $this->app->data['setting']['admin_domain'] . '?redirect_url=website/' . $website->getId() . '/payment',
                                'new_expiration_date' => $new_expiration_date,
                                'setting' => $setting
                            ]);
                            $mail->sendTo($account->getEmail(), 'Confirmation de paiement', $content);
                            return ['status' => 'success', 'message' => 'Merci ! Votre paiement à bien été pris en compte.', 'data' => compact('values', 'new_expiration_date')];
                        }
                    }
                    $log->addError('Erreur de paiement pour la référence : ' . $charge->id);
                    return ['status' => 'error', 'message' => 'Erreur lors du paiement !'];
                } catch (\Exception $e) {
                    $log->addError($e->getMessage());
                    return ['status' => 'error', 'message' => $e->getMessage()];
                }
            }
            return $response;
        }
        return ['status' => 'error', 'message' => 'Requête non autorisée'];
    }
}