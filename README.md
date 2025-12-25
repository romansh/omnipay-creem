# Omnipay-Creem

⚠️ **Early development.** The API is mostly stable and can be used, but may still change.
Examples and documentation are minimal and mostly illustrative.

## Installation

```bash
composer require romansh/omnipay-creem:^0.1.0
```

## Gateway Overview

This package provides a Creem Gateway for Omnipay.

It supports:
* Hosted Checkout (redirect)
* Webhook completion

## Usage Example

```php
use Omnipay\Omnipay;

$gateway = Omnipay::create('Creem');
$gateway->initialize([
    'apiKey' => 'your_api_key',
    'testMode' => true,
    'productId' => 'your_product_id',
]);

// Create a purchase request (Hosted Checkout)
$response = $gateway->purchase([
    'amount' => '10.00',
    'currency' => 'USD',
    'returnUrl' => 'https://your-site.com/return', // where user is redirected after payment
    'cancelUrl' => 'https://your-site.com/cancel', // optional
])->send();

// Redirect user to Creem Hosted Checkout
if ($response->isRedirect()) {
    $response->redirect(); // sends the user to Creem checkout
} else {
    echo "Payment error: " . $response->getMessage();
}

// Complete purchase (usually in webhook)
$complete = $gateway->completePurchase([
    'transactionReference' => 'txn_123',
])->send();
```

## Gateway Parameters

| Parameter | Description | Default |
|----------------|-----------------------------------|---------|
| `apiKey` | Your Creem API key | empty |
| `testMode` | Enable sandbox/test mode | true |
| `webhookSecret`| Secret for verifying webhook payloads | empty |
| `productId` | Creem Product ID | empty |

## Notes

- API is mostly stable, but changes may occur.
- Examples are illustrative only and may not cover all functionality.
- Hosted Checkout flow requires user redirection.
- Use at your own discretion; not yet fully documented for production use.
