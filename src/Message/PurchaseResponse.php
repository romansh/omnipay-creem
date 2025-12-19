<?php

namespace Omnipay\Creem\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;

/**
 * Creem Purchase Response
 *
 * This class handles the response from the checkout creation request.
 * Since Creem uses hosted checkouts, this response is responsible for
 * identifying and providing the redirection URL.
 */
class PurchaseResponse extends AbstractResponse implements RedirectResponseInterface
{
    /**
     * Is the response successful?
     * * In the context of a purchase request that requires a redirect, 
     * isSuccessful() usually returns false because the transaction 
     * is not yet complete.
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return false;
    }

    /**
     * Does the response require a redirect?
     *
     * @return bool
     */
    public function isRedirect(): bool
    {
        return isset($this->data['checkout_url']);
    }

    /**
     * Get the redirect URL.
     *
     * @return string|null
     */
    public function getRedirectUrl(): ?string
    {
        return $this->data['checkout_url'] ?? null;
    }

    /**
     * Get the redirect method.
     *
     * @return string
     */
    public function getRedirectMethod(): string
    {
        return 'GET';
    }

    /**
     * Get the redirect data (none required for GET redirects).
     *
     * @return array|null
     */
    public function getRedirectData(): ?array
    {
        return null;
    }

    /**
     * Get the transaction reference (Creem's internal Checkout ID).
     *
     * @return string|null
     */
    public function getTransactionReference(): ?string
    {
        return $this->data['id'] ?? null;
    }

    /**
     * Get the error message from the response if it exists.
     *
     * @return string|null
     */
    public function getMessage(): ?string
    {
        if (isset($this->data['error'])) {
            return (string) ($this->data['error']['message'] ?? 'Unknown error');
        }

        return $this->data['message'] ?? null;
    }

    /**
     * Get the error code from the response.
     *
     * @return string|null
     */
    public function getCode(): ?string
    {
        return isset($this->data['error']['code']) 
            ? (string) $this->data['error']['code'] 
            : null;
    }
}