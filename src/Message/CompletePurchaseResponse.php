<?php

namespace Omnipay\Creem\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;

class CompletePurchaseResponse extends AbstractResponse
{
    public function isSuccessful()
    {
        $eventType = $this->data['event_type'] ?? '';
        
        return $eventType === 'checkout.completed' || 
               $eventType === 'subscription.active';
    }

    public function isPending()
    {
        $eventType = $this->data['event_type'] ?? '';
        
        return $eventType === 'subscription.trialing';
    }

    public function isCancelled()
    {
        $eventType = $this->data['event_type'] ?? '';
        
        return $eventType === 'subscription.canceled' || 
               $eventType === 'subscription.expired';
    }

    public function isRefunded()
    {
        $eventType = $this->data['event_type'] ?? '';
        
        return $eventType === 'refund.created';
    }

    public function getTransactionReference()
    {
        return $this->data['data']['order_id'] ?? 
               $this->data['data']['subscription_id'] ?? null;
    }

    public function getTransactionId()
    {
        return $this->data['data']['request_id'] ?? null;
    }

    public function getEventType()
    {
        return $this->data['event_type'] ?? null;
    }

    public function getMessage()
    {
        return $this->data['message'] ?? null;
    }

    public function getCode()
    {
        return $this->data['event_type'] ?? null;
    }

    public function getCustomer()
    {
        return $this->data['data']['customer'] ?? null;
    }

    public function getProduct()
    {
        return $this->data['data']['product'] ?? null;
    }
}
