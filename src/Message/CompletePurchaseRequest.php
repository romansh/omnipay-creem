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
        // Get data from webhook callback
        $data = $this->httpRequest->request->all();
        
        if (empty($data)) {
            $data = json_decode($this->httpRequest->getContent(), true);
        }

        // Validate signature if webhook secret is provided
        $signature = $this->httpRequest->headers->get('x-creem-signature');
        
        if ($this->getWebhookSecret() && !$this->validateSignature($data, $signature)) {
            throw new InvalidResponseException('Invalid webhook signature');
        }

        return $data;
    }

    public function sendData($data)
    {
        return $this->response = new CompletePurchaseResponse($this, $data);
    }

    protected function validateSignature($data, $signature)
    {
        if (!$signature) {
            return false;
        }

        $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $expectedSignature = hash_hmac('sha256', $jsonData, $this->getWebhookSecret());

        return hash_equals($expectedSignature, $signature);
    }
}
