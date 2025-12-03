<?php

namespace Omnipay\Creem\Message;

use Omnipay\Common\Message\AbstractResponse;

class CustomerPortalResponse extends AbstractResponse
{
    public function isSuccessful()
    {
        return isset($this->data['url']) && !isset($this->data['error']);
    }

    public function getPortalUrl()
    {
        return $this->data['url'] ?? null;
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
