<?php

namespace Jet\Modules\Payment\Models;

use Jet\Models\Account;
use Jet\Models\Address;
use Jet\Models\Website;
use JetFire\Db\Model;
use Doctrine\ORM\Mapping;

/**
 * Class Payment
 * @package Jet\Models
 * @Entity(repositoryClass="Jet\Modules\Payment\Models\PaymentRepository")
 * @Table(name="payments")
 * @HasLifecycleCallbacks
 */
class Payment extends Model implements \JsonSerializable
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    /**
     * @Column(type="string", unique=true)
     */
    protected $reference;
    /**
     * @Column(type="string")
     */
    protected $title;
    /**
     * @Column(type="string")
     */
    protected $type;
    /**
     * @Column(type="float")
     */
    protected $amount;
    /**
     * @Column(type="float")
     */
    protected $tax;
    /**
     * @Column(type="string", length=50)
     */
    protected $currency;
    /**
     * @ManyToOne(targetEntity="Jet\Models\Account")
     * @JoinColumn(name="account_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $account;
    /**
     * @ManyToOne(targetEntity="Jet\Models\Website")
     * @JoinColumn(name="website_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $website;
    /**
     * @ManyToOne(targetEntity="Jet\Models\Address")
     * @JoinColumn(name="invoice_address_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $invoice_address;
    /**
     * @Column(type="datetime")
     */
    public $created_at;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param mixed $reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @param mixed $tax
     */
    public function setTax($tax)
    {
        $this->tax = $tax;
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param mixed $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return Address
     */
    public function getInvoiceAddress()
    {
        return $this->invoice_address;
    }

    /**
     * @param mixed $invoice_address
     */
    public function setInvoiceAddress($invoice_address)
    {
        $this->invoice_address = $invoice_address;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param mixed $created_at
     */
    public function setCreatedAt(\DateTime $created_at)
    {
        $this->created_at = $created_at;
    }

    /**
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param Account $account
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;
    }

    /**
     * @return Website
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @param Website $website
     */
    public function setWebsite(Website $website)
    {
        $this->website = $website;
    }

    /**
     * @PrePersist
     */
    public function onPrePersist(){
        $this->setCreatedAt(new \DateTime('now'));
    }


    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'reference' => $this->getReference(),
            'title' => $this->getTitle(),
            'type' => $this->getType(),
            'amount' => $this->getAmount(),
            'tax' => $this->getTax(),
            'currency' => $this->getCurrency(),
            'invoice_address' => $this->getInvoiceAddress(),
            'account' => $this->getAccount(),
            'website' => [
                'id' => $this->getWebsite()->getId(),
                'domain' => $this->getWebsite()->getDomain()
            ],
            'created_at' => $this->getCreatedAt()
        ];
    }
}