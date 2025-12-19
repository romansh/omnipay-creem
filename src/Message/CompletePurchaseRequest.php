<?php

namespace Omnipay\Creem\Message;

use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Message\AbstractRequest;

/**
 * Creem Complete Purchase Request
 *
 * This class handles the incoming webhook from Creem.io,
 * validates the SHA256 signature, and prepares the data for the response.
 */
class CompletePurchaseRequest extends AbstractRequest
{
    /**
     * Internal cache for the validated and decoded data.
     *
     * @var array|null
     */
    protected $data;

    /**
     * Get the API Key.
     *
     * @return string|null
     */
    public function getApiKey()
    {
        return $this->getParameter('apiKey');
    }

    /**
     * Set the API Key.
     *
     * @param string $value
     * @return $this
     */
    public function setApiKey($value)
    {
        return $this->setParameter('apiKey', $value);
    }

    /**
     * Get the Webhook Secret.
     *
     * @return string|null
     */
    public function getWebhookSecret()
    {
        return $this->getParameter('webhookSecret');
    }

    /**
     * Set the Webhook Secret.
     *
     * @param string $value
     * @return $this
     */
    public function setWebhookSecret($value)
    {
        return $this->setParameter('webhookSecret', $value);
    }

    /**
     * Get and validate the data from the HTTP request.
     *
     * This method decodes the JSON payload and verifies the creem-signature header.
     * The results are cached internally to prevent multiple signature checks.
     *
     * @throws InvalidResponseException If JSON is invalid, secret is missing, or signature check fails.
     * @return array
     */
    public function getData()
    {
        // Return cached data if signature has already been validated
        if ($this->data !== null) {
            return $this->data;
        }

        $content = $this->httpRequest->getContent();
        $data = json_decode($content, true);
        
        // Ensure payload is valid JSON
        if (!$data) {
            throw new InvalidResponseException('Invalid webhook payload: not valid JSON');
        }
        
        // Ensure webhook secret is available for verification
        $webhookSecret = $this->getWebhookSecret();
        if (!$webhookSecret) {
            throw new InvalidResponseException('Webhook secret is not configured. Please set webhookSecret parameter.');
        }
        
        // Extract signature header sent by Creem
        $signature = $this->httpRequest->headers->get('creem-signature');
        if (!$signature) {
            throw new InvalidResponseException('Webhook signature header (creem-signature) is missing');
        }
        
        // Perform cryptographic signature verification
        if (!$this->validateSignature($content, $signature)) {
            throw new InvalidResponseException('Invalid webhook signature. The signature does not match the expected value.');
        }

        // Cache the result
        $this->data = $data;

        return $this->data;
    }

    /**
     * Send the data and return the response object.
     *
     * @param mixed $data The data returned from getData()
     * @return CompletePurchaseResponse
     */
    public function sendData($data)
    {
        return $this->response = new CompletePurchaseResponse($this, $data);
    }

    /**
     * Validate the HMAC SHA256 signature.
     *
     * @param string $payload The raw request body
     * @param string|null $signature The signature from headers
     * @return bool
     */
    protected function validateSignature(string $payload, ?string $signature): bool
    {
        if (!$signature) {
            return false;
        }

        // Generate expected signature using the raw payload and shared secret
        $expectedSignature = hash_hmac('sha256', $payload, $this->getWebhookSecret());

        // Use hash_equals to prevent timing attacks
        return hash_equals($expectedSignature, $signature);
    }
}