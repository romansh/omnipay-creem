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
        
        if (!$data) {
            throw new InvalidResponseException('Invalid webhook payload: not valid JSON');
        }
        
        $webhookSecret = $this->getWebhookSecret();
        if (!$webhookSecret) {
            throw new InvalidResponseException('Webhook secret is not configured. Please set webhookSecret parameter.');
        }
        
        $signature = $this->httpRequest->headers->get('creem-signature');
        if (!$signature) {
            throw new InvalidResponseException('Webhook signature header (creem-signature) is missing');
        }
        
        if (!$this->validateSignature($content, $signature)) {
            throw new InvalidResponseException('Invalid webhook signature. The signature does not match the expected value.');
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
