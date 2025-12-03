<?php

namespace Omnipay\Creem\Message;

use Omnipay\Common\Message\AbstractRequest;

class CustomerPortalRequest extends AbstractRequest
{
    protected $endpoint = 'https://api.creem.io/v1/customers/billing';
    protected $testEndpoint = 'https://test-api.creem.io/v1/customers/billing';

    public function getApiKey()
    {
        return $this->getParameter('apiKey');
    }

    public function setApiKey($value)
    {
        return $this->setParameter('apiKey', $value);
    }

    public function getCustomerId()
    {
        return $this->getParameter('customerId');
    }

    public function setCustomerId($value)
    {
        return $this->setParameter('customerId', $value);
    }

    public function getData()
    {
        $this->validate('customerId');

        return [
            'customer_id' => $this->getCustomerId(),
        ];
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

        return $this->response = new CustomerPortalResponse($this, $responseData);
    }
}
