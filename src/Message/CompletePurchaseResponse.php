<?php
namespace Omnipay\Creem\Message;

use Omnipay\Common\Message\AbstractResponse;

class CompletePurchaseResponse extends AbstractResponse
{
    /**
     * Check if the payment or subscription event was successful
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
     * Check if the subscription is in trial period
     */
    public function isPending(): bool
    {
        return $this->getEventType() === 'subscription.trialing';
    }

    /**
     * Check if the subscription was cancelled or expired
     */
    public function isCancelled(): bool
    {
        return in_array($this->getEventType(), [
            'subscription.canceled',
            'subscription.expired'
        ], true);
    }

    /**
     * Check if the event represents a refund
     */
    public function isRefunded(): bool
    {
        return $this->getEventType() === 'refund.created';
    }

    /**
     * Get the event type from the webhook payload
     */
    public function getEventType(): ?string
    {
        return $this->data['eventType'] ?? null;
    }

    /**
     * Get the internal order ID passed when creating the checkout
     */
    public function getTransactionId(): ?string
    {
        return $this->data['object']['request_id'] ?? null;
    }

    /**
     * Get the Creem checkout ID
     */
    public function getTransactionReference(): ?string
    {
        return $this->data['object']['id'] ?? $this->data['id'] ?? null;
    }

    /**
     * Get the status or message from the webhook payload
     */
    public function getMessage(): ?string
    {
        return $this->data['message'] ?? $this->data['object']['status'] ?? 'ok';
    }

    /**
     * Get the code for the event (here, just the event type)
     */
    public function getCode(): ?string
    {
        return $this->getEventType();
    }

    /**
     * Get customer information from the webhook payload
     */
    public function getCustomer(): ?array
    {
        return $this->data['object']['customer'] ?? null;
    }

    /**
     * Get product information from the webhook payload
     */
    public function getProduct(): ?array
    {
        return $this->data['object']['product'] ?? null;
    }

    /**
     * No redirect URL for webhook response
     */
    public function getRedirectUrl(): ?string { return null; }

    /**
     * No portal URL for webhook response
     */
    public function getPortalUrl(): ?string { return null; }
}
