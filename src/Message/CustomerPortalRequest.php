<?php

namespace Omnipay\Creem\Message;

use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Common\Message\ResponseInterface;

/**
 * Creem Customer Portal Request
 *
 * This request allows you to generate a unique URL for the Creem Customer Billing Portal,
 * where customers can manage their subscriptions and billing details.
 */
class CustomerPortalRequest extends AbstractRequest
{
    /**
     * @var string API Production endpoint for customer billing
     */
    protected string $endpoint = 'https://api.creem.io/v1/customers/billing';

    /**
     * @var string API Sandbox endpoint for customer billing
     */
    protected string $testEndpoint = 'https://test-api.creem.io/v1/customers/billing';

    /**
     * Get the API Key.
     *
     * @return string|null
     */
    public function getApiKey(): ?string
    {
        return $this->getParameter('apiKey');
    }

    /**
     * Set the API Key.
     *
     * @param string $value
     * @return self
     */
    public function setApiKey(string $value): self
    {
        return $this->setParameter('apiKey', $value);
    }

    /**
     * Get the Creem Customer ID.
     *
     * @return string|null
     */
    public function getCustomerId(): ?string
    {
        return $this->getParameter('customerId');
    }

    /**
     * Set the Creem Customer ID.
     *
     * @param string $value
     * @return self
     */
    public function setCustomerId(string $value): self
    {
        return $this->setParameter('customerId', $value);
    }

    /**
     * Prepare the data for the API request.
     *
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     * @return array<string, string>
     */
    public function getData(): array
    {
        $this->validate('customerId');

        return [
            'customer_id' => (string) $this->getCustomerId(),
        ];
    }

    /**
     * Send the request to Creem API.
     *
     * @param mixed $data The data from getData()
     * @return ResponseInterface
     */
    public function sendData(mixed $data): ResponseInterface
    {
        $url = $this->getTestMode() ? $this->testEndpoint : $this->endpoint;
        
        $headers = [
            'Content-Type' => 'application/json',
            'x-api-key'    => $this->getApiKey(),
        ];

        $httpResponse = $this->httpClient->request(
            'POST',
            $url,
            $headers,
            json_encode($data)
        );

        $responseData = json_decode($httpResponse->getBody()->getContents(), true);

        return $this->response = new CustomerPortalResponse($this, (array) $responseData);
    }
}