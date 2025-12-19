<?php

namespace Omnipay\Creem\Message;

use Omnipay\Common\Message\AbstractResponse;

/**
 * Creem Complete Purchase Response
 *
 * This class handles the response (usually from a webhook) after a payment 
 * or subscription event has occurred. It maps Creem event types to 
 * standard Omnipay response states.
 */
class CompletePurchaseResponse extends AbstractResponse
{
    /**
     * Check if the payment or subscription event was successful.
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return in_array($this->getEventType(), [
            'checkout.completed',
            'subscription.active',
            'subscription.renewed'
        ], true);
    }

    /**
     * Check if the subscription is in a trial period (considered pending).
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->getEventType() === 'subscription.trialing';
    }

    /**
     * Check if the subscription was cancelled or expired.
     *
     * @return bool
     */
    public function isCancelled(): bool
    {
        return in_array($this->getEventType(), [
            'subscription.canceled',
            'subscription.expired'
        ], true);
    }

    /**
     * Check if the event represents a refund.
     *
     * @return bool
     */
    public function isRefunded(): bool
    {
        return $this->getEventType() === 'refund.created';
    }

    /**
     * Get the event type from the webhook payload.
     *
     * @return string|null
     */
    public function getEventType(): ?string
    {
        return isset($this->data['eventType']) ? (string) $this->data['eventType'] : null;
    }

    /**
     * Get the internal order ID passed during checkout creation.
     *
     * @return string|null
     */
    public function getTransactionId(): ?string
    {
        return isset($this->data['object']['request_id']) 
            ? (string) $this->data['object']['request_id'] 
            : null;
    }

    /**
     * Get the Creem checkout or object ID reference.
     *
     * @return string|null
     */
    public function getTransactionReference(): ?string
    {
        return (string) ($this->data['object']['id'] ?? $this->data['id'] ?? null);
    }

    /**
     * Get the status or message from the webhook payload.
     *
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return (string) ($this->data['message'] ?? $this->data['object']['status'] ?? 'ok');
    }

    /**
     * Get the code for the event (mapping to eventType).
     *
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->getEventType();
    }

    /**
     * Get customer information from the webhook payload.
     *
     * @return array|null
     */
    public function getCustomer(): ?array
    {
        return isset($this->data['object']['customer']) 
            ? (array) $this->data['object']['customer'] 
            : null;
    }

    /**
     * Get product information from the webhook payload.
     *
     * @return array|null
     */
    public function getProduct(): ?array
    {
        return isset($this->data['object']['product']) 
            ? (array) $this->data['object']['product'] 
            : null;
    }

    /**
     * No redirect URL is applicable for webhook/complete purchase responses.
     *
     * @return string|null
     */
    public function getRedirectUrl(): ?string 
    { 
        return null; 
    }

    /**
     * No portal URL is applicable for this response type.
     *
     * @return string|null
     */
    public function getPortalUrl(): ?string 
    { 
        return null; 
    }
}