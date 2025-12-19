<?php

namespace Omnipay\Creem\Message;

use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Common\Message\ResponseInterface;

/**
 * Creem Purchase Request
 *
 * This class prepares the data to create a checkout session in Creem.io
 * and sends it to the API. It expects 'apiKey', 'productId', and 'transactionId'.
 *
 * @method \Omnipay\Common\Message\ResponseInterface send()
 */
class PurchaseRequest extends AbstractRequest
{
    /**
     * @var string API Production endpoint
     */
    protected string $endpoint = 'https://api.creem.io/v1/checkouts';

    /**
     * @var string API Sandbox/Test endpoint
     */
    protected string $testEndpoint = 'https://test-api.creem.io/v1/checkouts';

    /**
     * Get the API Key used for authentication.
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
     * Get the Product ID associated with this checkout.
     *
     * @return string|null
     */
    public function getProductId(): ?string
    {
        return $this->getParameter('productId');
    }

    /**
     * Set the Product ID.
     *
     * @param string $value
     * @return self
     */
    public function setProductId(string $value): self
    {
        return $this->setParameter('productId', $value);
    }

    /**
     * Prepare the data for the API request payload.
     *
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        $this->validate('productId', 'transactionId');

        $data = [
            'product_id' => $this->getProductId(),
            'request_id' => $this->getTransactionId(),
        ];

        // Process customer data from CreditCard object
        if ($card = $this->getCard()) {
            $customer = [];
            
            if ($email = $card->getEmail()) {
                $customer['email'] = $email;
            }
            
            if (!empty($customer)) {
                $data['customer'] = $customer;
            }
        }

        // Process metadata
        $metadata = [];
        
        if ($card = $this->getCard()) {
            if ($name = $card->getName()) {
                $metadata['name'] = $name;
            }
        }
        
        if ($description = $this->getDescription()) {
            $metadata['description'] = $description;
        }

        if (!empty($metadata)) {
            $data['metadata'] = $metadata;
        }

        // Add return URL for redirection after payment
        if ($returnUrl = $this->getReturnUrl()) {
            $data['success_url'] = $returnUrl;
        }

        return $data;
    }

    /**
     * Send the request to Creem API and return a Response object.
     *
     * @param mixed $data The data returned by getData()
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

        // Ensure PurchaseResponse is returned for proper redirect handling
        return $this->response = new PurchaseResponse($this, $responseData);
    }
}