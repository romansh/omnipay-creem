**Warning: This package is currently in development and not ready for production use. API may change without notice.**

# Omnipay: Creem

**Creem driver for the Omnipay PHP payment processing library**

[Omnipay](https://github.com/thephpleague/omnipay) is a framework agnostic, multi-gateway payment
processing library for PHP. This package implements Creem support for Omnipay.

## About Creem

[Creem](https://creem.io/) is a financial OS for SaaS businesses that handles payments, subscriptions, tax compliance, and merchant of record services. It's designed for indie hackers, micro-SaaS, and startups selling software globally.

## Table of Contents

- [Installation](#installation)
- [Basic Usage](#basic-usage)
- [Advanced Usage](#advanced-usage)
  - [Webhook Handling](#webhook-handling)
  - [Customer Portal](#customer-portal)
- [Laravel Integration](#laravel-integration)
- [Important Notes](#important-notes)
- [Testing](#testing)
- [Contributing](#contributing)
- [Support](#support)
- [License](#license)

## Installation

This package requires PHP 7.4 or higher.

Install via Composer:

```bash
composer require omnipay/creem
```

## Basic Usage

### Initialize Gateway

```php
use Omnipay\Omnipay;

$gateway = Omnipay::create('Creem');
$gateway->setApiKey('your_api_key_here');
$gateway->setTestMode(true); // Set to false for production
```

### Create Checkout Session

```php
$response = $gateway->purchase([
    'productId' => 'prod_xxxxxxxxx',      // Product ID from Creem dashboard
    'transactionId' => 'ORDER-12345',     // Your unique order/request ID
    'card' => [
        'email' => 'customer@example.com',
        'name' => 'John Doe',
    ],
    'description' => 'Payment for premium plan',
])->send();

if ($response->isRedirect()) {
    // Redirect customer to Creem checkout
    return redirect($response->getRedirectUrl());
} else {
    // Handle error
    echo "Error: " . $response->getMessage();
}
```

## Advanced Usage

### Webhook Handling

Set up webhooks in your Creem dashboard and handle them in your application:

```php
// Configure webhook secret for signature validation (recommended)
$gateway->setParameter('webhookSecret', 'your_webhook_secret');

try {
    $response = $gateway->completePurchase()->send();
    
    // Get event type
    $eventType = $response->getEventType();
    
    switch ($eventType) {
        case 'checkout.completed':
            // One-time payment completed
            $orderId = $response->getTransactionReference();
            $requestId = $response->getTransactionId();
            $customer = $response->getCustomer();
            
            // Update your database
            // Order::where('id', $requestId)->update(['status' => 'paid']);
            break;
            
        case 'subscription.active':
            // Subscription activated - grant access
            break;
            
        case 'subscription.trialing':
            // Trial started - grant trial access
            break;
            
        case 'subscription.canceled':
        case 'subscription.expired':
            // Subscription ended - revoke access
            break;
            
        case 'refund.created':
            // Refund processed - handle refund logic
            break;
            
        case 'dispute.created':
            // Chargeback/dispute - handle dispute
            break;
    }
    
    // Return 200 OK to acknowledge receipt
    http_response_code(200);
    echo json_encode(['status' => 'ok']);
    
} catch (\Exception $e) {
    // Log error and return 400
    error_log('Webhook error: ' . $e->getMessage());
    http_response_code(400);
}
```

### Customer Portal

Generate a customer portal URL where customers can manage subscriptions and request refunds:

```php
$response = $gateway->customerPortal([
    'customerId' => 'cust_xxxxxxxxx', // Customer ID from Creem
])->send();

if ($response->isSuccessful()) {
    $portalUrl = $response->getPortalUrl();
    return redirect($portalUrl);
}
```

## Laravel Integration

### Configuration

Add to `config/services.php`:

```php
'creem' => [
    'api_key' => env('CREEM_API_KEY'),
    'webhook_secret' => env('CREEM_WEBHOOK_SECRET'),
    'test_mode' => env('CREEM_TEST_MODE', true),
],
```

Add to `.env`:

```env
CREEM_API_KEY=your_api_key
CREEM_WEBHOOK_SECRET=your_webhook_secret
CREEM_TEST_MODE=true
```

### Controller Example

```php
<?php

namespace App\Http\Controllers;

use Omnipay\Omnipay;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    private $gateway;
    
    public function __construct()
    {
        $this->gateway = Omnipay::create('Creem');
        $this->gateway->setApiKey(config('services.creem.api_key'));
        $this->gateway->setTestMode(config('services.creem.test_mode'));
    }
    
    public function createCheckout(Request $request)
    {
        $response = $this->gateway->purchase([
            'productId' => 'prod_xxxxxxxxx',
            'transactionId' => $request->order_id,
            'card' => [
                'email' => $request->user()->email,
                'name' => $request->user()->name,
            ],
        ])->send();
        
        if ($response->isRedirect()) {
            return redirect($response->getRedirectUrl());
        }
        
        return back()->withErrors(['payment' => $response->getMessage()]);
    }
    
    public function webhook(Request $request)
    {
        $this->gateway->setParameter('webhookSecret', config('services.creem.webhook_secret'));
        
        try {
            $response = $this->gateway->completePurchase()->send();
            
            switch ($response->getEventType()) {
                case 'checkout.completed':
                    Order::where('id', $response->getTransactionId())
                        ->update(['status' => 'paid']);
                    break;
                    
                case 'subscription.active':
                    // Grant subscription access
                    break;
                    
                case 'refund.created':
                    Order::where('id', $response->getTransactionId())
                        ->update(['status' => 'refunded']);
                    break;
            }
            
            return response()->json(['status' => 'ok']);
            
        } catch (\Exception $e) {
            \Log::error('Creem webhook error: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid webhook'], 400);
        }
    }
    
    public function customerPortal($customerId)
    {
        $response = $this->gateway->customerPortal([
            'customerId' => $customerId,
        ])->send();
        
        if ($response->isSuccessful()) {
            return redirect($response->getPortalUrl());
        }
        
        return back()->withErrors(['portal' => $response->getMessage()]);
    }
}
```

### Routes

```php
// routes/web.php
Route::post('/payment/create', [PaymentController::class, 'createCheckout'])->name('payment.create');
Route::get('/customer/portal/{customerId}', [PaymentController::class, 'customerPortal'])->name('customer.portal');

// routes/api.php
Route::post('/webhooks/creem', [PaymentController::class, 'webhook'])->name('webhooks.creem');
```

Don't forget to exclude the webhook route from CSRF protection in `app/Http/Middleware/VerifyCsrfToken.php`:

```php
protected $except = [
    'webhooks/creem',
];
```

## Important Notes

### Refunds

**Creem does not provide a direct API endpoint for refunds.** Refunds are handled through:

1. **Creem Dashboard** - Merchants can process refunds manually
2. **Customer Portal** - Customers can request refunds

Use the `customerPortal()` method to generate a portal URL where customers can manage their subscriptions and request refunds.

### Webhook Events

Creem supports the following webhook events:

- `checkout.completed` - One-time payment completed
- `subscription.active` - Subscription activated
- `subscription.trialing` - Trial period started
- `subscription.canceled` - Subscription canceled by customer
- `subscription.expired` - Subscription expired naturally
- `refund.created` - Refund processed
- `dispute.created` - Chargeback/dispute initiated

### Webhook Security

Always validate webhook signatures in production:

```php
$gateway->setParameter('webhookSecret', 'your_webhook_secret');
```

This ensures the webhook request is actually from Creem.

## Testing

You can use Creem's test mode:

```php
$gateway->setTestMode(true);
```

Test and production environments use different:
- API keys
- Webhook secrets
- Product IDs

Make sure to use test credentials when `testMode` is enabled.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

### Development Setup

```bash
git clone https://github.com/romansh/omnipay-creem.git
cd omnipay-creem
composer install
```

### Running Tests

```bash
composer test
```

## Support

If you are having general issues with Omnipay, we suggest posting on
[Stack Overflow](http://stackoverflow.com/). Be sure to add the
[omnipay tag](http://stackoverflow.com/questions/tagged/omnipay).

For Creem-specific questions:
- [Creem Documentation](https://docs.creem.io/)
- [Creem API Reference](https://docs.creem.io/api-reference)
- [Creem Support](https://creem.io/support)

If you believe you have found a bug in this package, please report it using the 
[GitHub issue tracker](https://github.com/romansh/omnipay-creem/issues).

## Resources

- [Creem Website](https://creem.io/)
- [Creem Documentation](https://docs.creem.io/)
- [Omnipay Documentation](https://omnipay.thephpleague.com/)

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
