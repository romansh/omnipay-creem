<?php

namespace Omnipay\Creem\Message;

use Omnipay\Common\Message\AbstractRequest;

class PurchaseRequest extends AbstractRequest
{
    protected $endpoint = 'https://api.creem.io/v1/checkouts';
    protected $testEndpoint = 'https://test-api.creem.io/v1/checkouts';

    public function getApiKey()
    {
        return $this->getParameter('apiKey');
    }

    public function setApiKey($value)
    {
        return $this->setParameter('apiKey', $value);
    }

    public function getProductId()
    {
        return $this->getParameter('productId');
    }

    public function setProductId($value)
    {
        return $this->setParameter('productId', $value);
    }

    public function getData()
    {
        $this->validate('productId', 'transactionId');

        $data = [
            'product_id' => $this->getProductId(),
            'request_id' => $this->getTransactionId(),
        ];

        // Add metadata if available
        $metadata = [];
        
        if ($card = $this->getCard()) {
            if ($card->getEmail()) {
                $metadata['email'] = $card->getEmail();
            }
            if ($card->getName()) {
                $metadata['name'] = $card->getName();
            }
        }
        
        if ($this->getDescription()) {
            $metadata['description'] = $this->getDescription();
        }

        if (!empty($metadata)) {
            $data['metadata'] = $metadata;
        }

        return $data;
    }

    public function sendData($data)
    {
        $url = $this->getTestMode() ? $this->testEndpoint : $this->endpoint;
        
        $headers = [
            'Content-Type' => 'application/json',
            'x-api-key' => $this->getApiKey(),
        ];

        $httpResponse = $this->httpClient->request(
            'POST',
            $url,
            $headers,
            json_encode($data)
        );

        $responseData = json_decode($httpResponse->getBody()->getContents(), true);

        return $this->response = new PurchaseResponse($this, $responseData);
    }
}
