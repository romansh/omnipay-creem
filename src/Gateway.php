<?php

namespace Omnipay\Creem;

use Omnipay\Common\AbstractGateway;
use Omnipay\Common\Message\AbstractRequest;

/**
 * Creem Gateway
 *
 * @link https://creem.io/
 */
class Gateway extends AbstractGateway
{
    public function getName()
    {
        return 'Creem';
    }

    public function getDefaultParameters()
    {
        return [
            'apiKey' => '',
            'testMode' => false,
        ];
    }

    public function getApiKey()
    {
        return $this->getParameter('apiKey');
    }

    public function setApiKey($value)
    {
        return $this->setParameter('apiKey', $value);
    }

    /**
     * Create a purchase request
     *
     * @param array $options
     * @return \Omnipay\Creem\Message\PurchaseRequest
     */
    public function purchase(array $options = [])
    {
        return $this->createRequest('\Omnipay\Creem\Message\PurchaseRequest', $options);
    }

    /**
     * Complete a purchase request
     *
     * @param array $options
     * @return \Omnipay\Creem\Message\CompletePurchaseRequest
     */
    public function completePurchase(array $options = [])
    {
        return $this->createRequest('\Omnipay\Creem\Message\CompletePurchaseRequest', $options);
    }

    /**
     * Generate customer portal URL
     * Customers can manage subscriptions and request refunds through the portal
     *
     * @param array $options
     * @return \Omnipay\Creem\Message\CustomerPortalRequest
     */
    public function customerPortal(array $options = [])
    {
        return $this->createRequest('\Omnipay\Creem\Message\CustomerPortalRequest', $options);
    }
}
