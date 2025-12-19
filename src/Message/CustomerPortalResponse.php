<?php

namespace Omnipay\Creem\Message;

use Omnipay\Common\Message\AbstractResponse;

/**
 * Creem Customer Portal Response
 *
 * This class handles the response from requests to generate a customer 
 * billing portal link, allowing users to manage their subscriptions.
 */
class CustomerPortalResponse extends AbstractResponse
{
    /**
     * Check if the request to generate the portal URL was successful.
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return isset($this->data['url']) && !isset($this->data['error']);
    }

    /**
     * Get the generated customer portal URL.
     *
     * @return string|null
     */
    public function getPortalUrl(): ?string
    {
        return $this->data['url'] ?? null;
    }

    /**
     * Get the error message if the request failed.
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
     * Get the error code if the request failed.
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