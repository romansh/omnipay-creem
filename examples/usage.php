<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Omnipay\Omnipay;

// Create gateway instance
$gateway = Omnipay::create('Creem');

$gateway->setApiKey('your_api_key_here');
$gateway->setTestMode(true); // false for production

// ============================================
// 1. Create payment checkout
// ============================================

try {
    $response = $gateway->purchase([
        'productId' => 'prod_xxxxxxxxx', // Product ID from Creem dashboard
        'transactionId' => 'ORDER-12345', // Your unique order/request ID
        'card' => [
            'email' => 'customer@example.com',
            'name' => 'John Doe',
        ],
        'description' => 'Payment for order #12345',
    ])->send();

    if ($response->isRedirect()) {
        // Redirect to Creem checkout page
        $response->redirect();
        
        // Or get redirect URL
        $checkoutUrl = $response->getRedirectUrl();
        echo "Go to checkout: {$checkoutUrl}\n";
        
        // Save checkout ID for reference
        $checkoutId = $response->getTransactionReference();
        // $_SESSION['checkout_id'] = $checkoutId;
        
    } else {
        echo "Error: " . $response->getMessage() . "\n";
    }
    
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

// ============================================
// 2. Handle webhook callback
// ============================================

try {
    // Set webhook secret for signature validation (optional but recommended)
    $gateway->setParameter('webhookSecret', 'your_webhook_secret');
    
    $response = $gateway->completePurchase()->send();

    $eventType = $response->getEventType();
    
    switch ($eventType) {
        case 'checkout.completed':
            echo "Payment completed!\n";
            echo "Order ID: " . $response->getTransactionReference() . "\n";
            echo "Request ID: " . $response->getTransactionId() . "\n";
            
            $customer = $response->getCustomer();
            echo "Customer email: " . ($customer['email'] ?? 'N/A') . "\n";
            
            // Update order status in database
            // updateOrderStatus($response->getTransactionId(), 'paid');
            break;
            
        case 'subscription.active':
            echo "Subscription activated!\n";
            // Grant access to the user
            break;
            
        case 'subscription.trialing':
            echo "Subscription trial started!\n";
            // Grant trial access
            break;
            
        case 'subscription.canceled':
        case 'subscription.expired':
            echo "Subscription ended!\n";
            // Revoke access
            break;
            
        case 'refund.created':
            echo "Refund processed!\n";
            // Handle refund
            break;
            
        case 'dispute.created':
            echo "Dispute created!\n";
            // Handle dispute/chargeback
            break;
            
        default:
            echo "Unknown event: {$eventType}\n";
    }
    
    // Return success response to Creem
    http_response_code(200);
    echo json_encode(['status' => 'ok']);
    
} catch (\Exception $e) {
    echo "Error processing webhook: " . $e->getMessage() . "\n";
    http_response_code(400);
}

// ============================================
// 3. Generate Customer Portal URL
// ============================================
// Customers can manage subscriptions and request refunds through the portal

try {
    $response = $gateway->customerPortal([
        'customerId' => 'cust_xxxxxxxxx', // Customer ID from Creem
    ])->send();

    if ($response->isSuccessful()) {
        $portalUrl = $response->getPortalUrl();
        echo "Customer portal URL: {$portalUrl}\n";
        
        // Redirect customer to portal
        // header("Location: {$portalUrl}");
        
    } else {
        echo "Error: " . $response->getMessage() . "\n";
    }
    
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

// ============================================
// 4. Laravel example
// ============================================

/*
// In config/services.php
'creem' => [
    'api_key' => env('CREEM_API_KEY'),
    'webhook_secret' => env('CREEM_WEBHOOK_SECRET'),
    'test_mode' => env('CREEM_TEST_MODE', true),
],

// In controller
use Omnipay\Omnipay;

class PaymentController extends Controller
{
    private $gateway;
    
    public function __construct()
    {
        $this->gateway = Omnipay::create('Creem');
        $this->gateway->setApiKey(config('services.creem.api_key'));
        $this->gateway->setTestMode(config('services.creem.test_mode'));
    }
    
    public function create(Request $request)
    {
        $response = $this->gateway->purchase([
            'productId' => config('services.creem.product_id'),
            'transactionId' => $request->order_id,
            'card' => [
                'email' => $request->email,
                'name' => $request->name,
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
    }
    
    public function portal($customerId)
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
*/
