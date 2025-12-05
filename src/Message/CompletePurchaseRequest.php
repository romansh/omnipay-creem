<?php

namespace Omnipay\Creem\Message;

use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Message\AbstractRequest;

class CompletePurchaseRequest extends AbstractRequest
{
    public function getApiKey()
    {
        return $this->getParameter('apiKey');
    }

    public function setApiKey($value)
    {
        return $this->setParameter('apiKey', $value);
    }

    public function getWebhookSecret()
    {
        return $this->getParameter('webhookSecret');
    }

    public function setWebhookSecret($value)
    {
        return $this->setParameter('webhookSecret', $value);
    }

    public function getData()
    {
        $content = $this->httpRequest->getContent();
        $data = json_decode($content, true);
        $signature = $this->httpRequest->headers->get('creem-signature');

        if ($this->getWebhookSecret() && !$this->validateSignature($content, $signature)) {
            throw new InvalidResponseException('Invalid webhook signature');
        }

        return $data;
    }

    public function sendData($data)
    {
        return $this->response = new CompletePurchaseResponse($this, $data);
    }

    protected function validateSignature(string $payload, ?string $signature): bool
    {
        if (!$signature) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $this->getWebhookSecret());
        return hash_equals($expectedSignature, $signature);
    }
}
