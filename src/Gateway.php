<?php

namespace Omnipay\Creem;

use Omnipay\Common\AbstractGateway;

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
            'webhookSecret' => ''
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
     * Get the webhook secret key
     */
    public function getWebhookSecret()
    {
        return $this->getParameter('webhookSecret');
    }

    /**
     * Set the webhook secret key
     */
    public function setWebhookSecret($value) 
    {
        return $this->setParameter('webhookSecret', $value);
    }

    /**
     * Replace the internal HTTP Request object with a new one.
     * This is crucial for webhooks to inject the Laravel Request.
     * * @param \Symfony\Component\HttpFoundation\Request $httpRequest
     * @return $this
     */
    public function setHttpRequest(\Symfony\Component\HttpFoundation\Request $httpRequest)
    {
        $this->httpRequest = $httpRequest;
        return $this;
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
