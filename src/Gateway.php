<?php

namespace Omnipay\Creem;

use Omnipay\Common\AbstractGateway;
use Omnipay\Creem\Message\PurchaseRequest;
use Omnipay\Creem\Message\CompletePurchaseRequest;
use Omnipay\Creem\Message\CustomerPortalRequest;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * Creem Gateway
 *
 * @link https://creem.io/
 * * @method \Omnipay\Common\Message\NotificationInterface acceptNotification(array $options = [])
 * @method \Omnipay\Common\Message\RequestInterface authorize(array $options = [])
 * @method \Omnipay\Common\Message\RequestInterface completeAuthorize(array $options = [])
 * @method \Omnipay\Common\Message\RequestInterface capture(array $options = [])
 * @method \Omnipay\Common\Message\RequestInterface refund(array $options = [])
 * @method \Omnipay\Common\Message\RequestInterface fetchTransaction(array $options = [])
 * @method \Omnipay\Common\Message\RequestInterface void(array $options = [])
 * @method \Omnipay\Common\Message\RequestInterface createCard(array $options = [])
 * @method \Omnipay\Common\Message\RequestInterface updateCard(array $options = [])
 * @method \Omnipay\Common\Message\RequestInterface deleteCard(array $options = [])
 */
class Gateway extends AbstractGateway
{
    /**
     * Get the human-readable name of the gateway.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Creem';
    }

    /**
     * Get the default parameters for the gateway.
     *
     * @return array<string, mixed>
     */
    public function getDefaultParameters(): array
    {
        return [
            'apiKey' => '',
            'testMode' => true,
            'webhookSecret' => '',
            'productId' => ''
        ];
    }

    /**
     * Get the API Key.
     *
     * @return string|null
     */
    public function getApiKey(): ?string
    {
        return $this->getParameter('apiKey');
    }

    /**
     * Set the API Key.
     *
     * @param string $value
     * @return self
     */
    public function setApiKey(string $value): self
    {
        return $this->setParameter('apiKey', $value);
    }

    /**
     * Get the webhook secret key for signature verification.
     *
     * @return string|null
     */
    public function getWebhookSecret(): ?string
    {
        return $this->getParameter('webhookSecret');
    }

    /**
     * Set the webhook secret key.
     *
     * @param string $value
     * @return self
     */
    public function setWebhookSecret(string $value): self
    {
        return $this->setParameter('webhookSecret', $value);
    }

    /**
     * Get the Creem Product ID.
     *
     * @return string|null
     */
    public function getProductId(): ?string
    {
        return $this->getParameter('productId');
    }

    /**
     * Set the Creem Product ID.
     *
     * @param string $value
     * @return self
     */
    public function setProductId(string $value): self
    {
        return $this->setParameter('productId', $value);
    }

    /**
     * Replace the internal HTTP Request object.
     * Crucial for webhooks to handle the payload correctly.
     *
     * @param SymfonyRequest $httpRequest
     * @return self
     */
    public function setHttpRequest(SymfonyRequest $httpRequest): self
    {
        $this->httpRequest = $httpRequest;
        return $this;
    }

    /**
     * Create a purchase request.
     *
     * @param array<string, mixed> $options
     * @return PurchaseRequest
     */
    public function purchase(array $options = []): PurchaseRequest
    {
        return $this->createRequest(PurchaseRequest::class, $options);
    }

    /**
     * Handle the completion of a purchase (webhooks).
     *
     * @param array<string, mixed> $options
     * @return CompletePurchaseRequest
     */
    public function completePurchase(array $options = []): CompletePurchaseRequest
    {
        return $this->createRequest(CompletePurchaseRequest::class, $options);
    }

    /**
     * Generate a customer portal URL for subscription management.
     *
     * @param array<string, mixed> $options
     * @return CustomerPortalRequest
     */
    public function customerPortal(array $options = []): CustomerPortalRequest
    {
        return $this->createRequest(CustomerPortalRequest::class, $options);
    }
}