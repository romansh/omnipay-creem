<?php

namespace Omnipay\Creem\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;

class PurchaseResponse extends AbstractResponse implements RedirectResponseInterface
{
    public function isSuccessful()
    {
        return false; // Always requires redirect for payment
    }

    public function isRedirect()
    {
        return isset($this->data['checkout_url']);
    }

    public function getRedirectUrl()
    {
        return $this->data['checkout_url'] ?? null; 
    }

    public function getRedirectMethod()
    {
        return 'GET';
    }

    public function getRedirectData()
    {
        return null;
    }

    public function getTransactionReference()
    {
        return $this->data['id'] ?? null; // ID checkout session
    }

    public function getMessage()
    {
        if (isset($this->data['error'])) {
            return $this->data['error']['message'] ?? 'Unknown error';
        }

        return $this->data['message'] ?? null;
    }

    public function getCode()
    {
        return $this->data['error']['code'] ?? null;
    }
}