<?php

namespace Jet\Modules\Payment\Controllers;

use Jet\AdminBlock\Controllers\AdminController;
use Jet\Models\Account;
use Jet\Models\Website;
use Jet\Modules\Payment\Models\Payment;
use Jet\Modules\Payment\Requests\PaymentRequest;
use Jet\Services\Auth;
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
     * @param PaymentRequest $request
     * @param Auth $auth
     * @return array
     */
    public function all(PaymentRequest $request, Auth $auth)
    {
        return $this->getWebsitePayments($request, $auth);
    }

    /**
     * @param PaymentRequest $request
     * @param Auth $auth
     * @param $website
     * @return array
     */
    public function getWebsitePayments(PaymentRequest $request, Auth $auth, $website = null)
    {
        $max = ($request->has('length')) ? (int)$request->query('length') : 10;
        $start = ($request->has('start')) ? (int)$request->query('start') : 1;

        if (!is_null($website) && !$this->isWebsiteOwner($auth, $website))
            return ['status' => 'error', 'message' => 'Vous n\'avez pas les permissions pour voir ces contenus'];

        $params = [
            'website' => $website,
            'order' => ($request->has('order')) ? $request->query('order') : [],
            'search' => $request->query('search')['value']
        ];
        $response = Payment::repo()->listAll($start, $max, $params);
        $payments = [
            'draw' => (int)$request->query('draw'),
            'recordsTotal' => $response['total'],
            'recordsFiltered' => $response['total'],
            'data' => $response['data']
        ];
        return $payments;
    }

    /**
     * @return string
     */
    public function getPaymentParams()
    {
        $data = [];
        if(isset($this->app->data['setting']['payment'])) {
            unset($this->app->data['setting']['payment']['stripe']['secret_key']);
            $data = $this->app->data['setting']['payment'];
        }
        return $data;
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
            if ($response === true) {

                /** @var Website $website */
                $website = Website::findOneById($website);
                if (is_null($website)) return ['status' => 'error', 'message' => 'Impossible de trouver le site'];

                /** @var Account $account */
                $account = Account::repo()->getWebsiteAccount($website);
                if (is_null($account)) return ['status' => 'error', 'message' => 'Impossible de trouver le compte associé avec votre site'];

                $values = $request->values();
                try {
                    Stripe::setApiKey($this->app->data['setting']['payment']['stripe']['secret_key']);
                    $tva = $this->app->data['setting']['payment']['stripe']['tva'];
                    $amount = (($tva / 100) + 1) * $values['total'];
                    $charge = Charge::create([
                        'amount' => number_format($amount, 2, '.', ' ') * 100,
                        'currency' => $this->app->data['setting']['payment']['stripe']['currency'],
                        'description' => "Abonnement de {$values['month']} mois",
                        'source' => $values['stripeToken'],
                        'receipt_email' => $account->getEmail()
                    ]);
                    if ($charge->paid === true) {
                        $payment = [
                            'title' => "Abonnement de {$values['month']} mois",
                            'amount' => $values['total'],
                            'tax' => $tva,
                            'currency' => $charge->currency,
                            'reference' => $charge->id,
                            'account' => $account,
                            'website' => $website
                        ];
                        $interval = new \DateInterval('P' . $values['month'] . 'M');
                        $new_expiration_date = $website->getExpirationDate()->add($interval);
                        if (Payment::create($payment) && Website::where('id', $website->getId())->set(['expiration_date' => $new_expiration_date, 'state' => 1])) {
                            $content = $this->render('Mail/payment_accepted', [
                                'values' => $values,
                                'account' => $account,
                                'url' => $this->app->data['setting']['admin_domain'] . '?redirect_url=website/' . $website->getId() . '/payment',
                                'new_expiration_date' => $new_expiration_date
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

    /**
     * @param $website
     * @return array
     */
    public function getPaymentDetails($website)
    {
        $data = [
            'count_payments' => 0,
            'currency' => $this->app->data['setting']['payment']['stripe']['currency'],
            'tva' => $this->app->data['setting']['payment']['stripe']['tva'],
            'last_payment_date' => null,
            'last_payment_amount' => null
        ];
        $payments = Payment::select('id', 'amount', 'currency', 'created_at')->where('website', $website)->get();
        if (!is_null($payments)) {
            $payments = $payments->getResults();
            $data['count_payments'] = count($payments);
            if ($data['count_payments'] > 0) {
                $data['currency'] = $payments[$data['count_payments'] - 1]['currency'];
                $data['last_payment_date'] = $payments[$data['count_payments'] - 1]['created_at'];
                $data['last_payment_amount'] = $payments[$data['count_payments'] - 1]['amount'];
            }
        }
        return $data;
    }
}