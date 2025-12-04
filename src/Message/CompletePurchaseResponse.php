<?php

namespace Omnipay\Creem\Message;

use Omnipay\Common\Message\AbstractResponse;

class CompletePurchaseResponse extends AbstractResponse
{
    public function isSuccessful(): bool
    {
        return in_array($this->getEventType(), [
            'checkout.completed',
            'subscription.active',
            'subscription.renewed'
        ], true);
    }

    public function isPending(): bool
    {
        return $this->getEventType() === 'subscription.trialing';
    }

    public function isCancelled(): bool
    {
        return in_array($this->getEventType(), [
            'subscription.canceled',
            'subscription.expired'
        ], true);
    }

    public function isRefunded(): bool
    {
        return $this->getEventType() === 'refund.created';
    }

    public function getEventType(): ?string
    {
        return $this->data['event_type'] ?? null;
    }

    public function getTransactionId(): ?string
    {
        return $this->data['payload']['metadata']['order_id']
            ?? $this->data['metadata']['order_id']
            ?? null;
    }

    public function getTransactionReference(): ?string
    {
        return $this->data['payload']['id'] ?? $this->data['id'] ?? null;
    }

    public function getMessage(): ?string
    {
        return $this->data['message'] ?? $this->data['payload']['status'] ?? 'ok';
    }

    public function getCode(): ?string
    {
        return $this->data['event_type'] ?? null;
    }

    public function getCustomer(): ?array
    {
        return $this->data['payload']['customer'] ?? $this->data['customer'] ?? null;
    }

    public function getProduct(): ?array
    {
        return $this->data['payload']['product'] ?? $this->data['product'] ?? null;
    }

    public function getRedirectUrl(): ?string { return null; }
    public function getPortalUrl(): ?string { return null; }
}