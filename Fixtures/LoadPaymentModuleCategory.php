<?php
namespace Jet\Modules\Payment\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Jet\Services\LoadFixture;

class LoadPaymentModuleCategory extends AbstractFixture
{
    use LoadFixture;

    protected $data = [
        'name' => 'Payment',
        'title' => 'Paiement',
        'slug' => 'payment',
        'description' => 'Module de paiement pour webzy',
        'icon' => 'fa fa-cc-stripe',
        'author' => 'S.Sumugan',
        'version' => '0.1',
        'update_available' => false,
        'access_level' => 4
    ];

    public function load(ObjectManager $manager)
    {
        $this->loadModuleCategory($manager);
    }
}