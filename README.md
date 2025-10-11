# Common Stripe

A modern PHP library for interacting with the Stripe API, built for Laravel applications. This package provides a clean, type-safe interface for working with Stripe
customers, products, prices, subscriptions, and financial connections.

## Features

- **Type-safe object models** - Strongly-typed PHP objects for Stripe entities
- **Service classes** - Clean service layer for common Stripe operations
- **Laravel integration** - Seamless integration with Laravel applications
- **Enum support** - PHP 8.3+ enums for Stripe constants
- **Financial Connections** - Support for Stripe Financial Connections API
- **PHPStan Level 9** - Maximum static analysis coverage

## Requirements

- PHP 8.3 or higher
- Laravel 11 or 12
- Stripe PHP SDK 16.5+ or 17.0+

## Installation

Install the package via Composer:

```bash
composer require encoredigitalgroup/common-stripe
```

The package will automatically register its service provider in Laravel.

## Configuration

Configure your Stripe API keys using the configuration system:

```php
use EncoreDigitalGroup\Common\Stripe\Support\Config\StripeConfig;

$config = StripeConfig::make();
$config->authentication->secretKey = env('STRIPE_SECRET_KEY');
$config->authentication->publicKey = env('STRIPE_PUBLIC_KEY');
```

Or in your Laravel `.env` file:

```env
STRIPE_SECRET_KEY=sk_test_...
STRIPE_PUBLIC_KEY=pk_test_...
```

## Usage

### Creating Stripe Objects

Create Stripe objects using the fluent factory methods:

```php
use EncoreDigitalGroup\Common\Stripe\Stripe;
use EncoreDigitalGroup\Common\Stripe\Objects\Support\StripeAddress;

$customer = Stripe::customer(
    name: 'John Doe',
    email: 'john@example.com',
    phone: '+1234567890',
    address: StripeAddress::make(
        line1: '123 Main St',
        city: 'San Francisco',
        state: 'CA',
        postalCode: '94111',
        country: 'US'
    )
);
```

### Customer Service

Manage customers using the customer service:

```php
use EncoreDigitalGroup\Common\Stripe\Stripe;

$service = Stripe::customers();

// Create a customer
$customer = $service->create($customer);

// Get a customer
$customer = $service->get('cus_xxxxx');

// Update a customer
$updated = $service->update('cus_xxxxx', $customer);

// Delete a customer
$service->delete('cus_xxxxx');

// List customers
$customers = $service->list(['limit' => 10]);

// Search customers
$results = $service->search('email:"john@example.com"');
```

### Product Service

Manage products and prices:

```php
use EncoreDigitalGroup\Common\Stripe\Services\StripeProductService;
use EncoreDigitalGroup\Common\Stripe\Objects\Product\StripeProduct;

$productService = StripeProductService::make();

// Create a product
$product = StripeProduct::make(
    name: 'Premium Plan',
    description: 'Our premium subscription plan',
    active: true
);

$created = $productService->create($product);

// Get a product
$product = $productService->get('prod_xxxxx');

// List products
$products = $productService->list(['active' => true]);
```

### Price Service

Work with Stripe prices:

```php
use EncoreDigitalGroup\Common\Stripe\Services\StripePriceService;
use EncoreDigitalGroup\Common\Stripe\Objects\Product\StripePrice;
use EncoreDigitalGroup\Common\Stripe\Enums\PriceType;
use EncoreDigitalGroup\Common\Stripe\Enums\RecurringInterval;

$priceService = StripePriceService::make();

// Create a recurring price
$price = StripePrice::make(
    product: 'prod_xxxxx',
    currency: 'usd',
    unitAmount: 1999, // $19.99
    type: PriceType::Recurring,
    recurring: [
        'interval' => RecurringInterval::Month,
        'interval_count' => 1
    ]
);

$created = $priceService->create($price);
```

### Subscription Service

Manage subscriptions:

```php
use EncoreDigitalGroup\Common\Stripe\Services\StripeSubscriptionService;
use EncoreDigitalGroup\Common\Stripe\Objects\Subscription\StripeSubscription;

$subscriptionService = StripeSubscriptionService::make();

// Create a subscription
$subscription = StripeSubscription::make(
    customer: 'cus_xxxxx',
    items: [
        ['price' => 'price_xxxxx', 'quantity' => 1]
    ]
);

$created = $subscriptionService->create($subscription);

// Get a subscription
$subscription = $subscriptionService->get('sub_xxxxx');

// Cancel a subscription
$subscriptionService->cancel('sub_xxxxx');
```

### Financial Connections

Work with Stripe Financial Connections:

```php
use EncoreDigitalGroup\Common\Stripe\Objects\FinancialConnections\StripeFinancialConnection;

$connection = StripeFinancialConnection::make(
    accountId: 'fca_xxxxx',
    accountHolderName: 'John Doe'
);
```

### Webhooks

Handle Stripe webhooks:

```php
use EncoreDigitalGroup\Common\Stripe\Stripe;

$webhook = Stripe::webhook(
    event: $request->input('type'),
    payload: $request->all()
);
```

## Available Objects

### Customer Objects

- `StripeCustomer` - Customer information
- `StripeAddress` - Customer address
- `StripeShipping` - Shipping information

### Product Objects

- `StripeProduct` - Product information
- `StripePrice` - Price information with recurring options

### Subscription Objects

- `StripeSubscription` - Subscription information

### Financial Connection Objects

- `StripeFinancialConnection` - Financial connection details
- `StripeBankAccount` - Bank account information
- `StripeTransactionRefresh` - Transaction refresh data

### Support Objects

- `StripeWebhook` - Webhook event handling

## Available Enums

Type-safe enums for Stripe constants:

- `BillingScheme` - per_unit, tiered
- `CollectionMethod` - charge_automatically, send_invoice
- `PriceType` - one_time, recurring
- `RecurringAggregateUsage` - sum, last_during_period, last_ever, max
- `RecurringInterval` - day, week, month, year
- `RecurringUsageType` - metered, licensed
- `SubscriptionStatus` - incomplete, incomplete_expired, trialing, active, past_due, canceled, unpaid, paused
- `TaxBehavior` - exclusive, inclusive, unspecified
- `TiersMode` - graduated, volume

## Object Conversion

All objects support conversion to/from Stripe API objects:

```php
// From Stripe API object
$customer = StripeCustomer::fromStripeObject($stripeApiCustomer);

// To array (for API requests)
$data = $customer->toArray();
```

## Services

All services extend the base `HasStripe` trait which provides:

- Automatic Stripe client initialization
- Configuration management
- Static factory methods

Available services:

- `StripeCustomerService` - Customer CRUD operations
- `StripeProductService` - Product management
- `StripePriceService` - Price management
- `StripeSubscriptionService` - Subscription management

## Error Handling

All service methods may throw Stripe API exceptions:

```php
use Stripe\Exception\ApiErrorException;

try {
    $customer = $service->get('cus_invalid');
} catch (ApiErrorException $e) {
    // Handle Stripe API error
    logger()->error('Stripe API error', [
        'message' => $e->getMessage(),
        'code' => $e->getStripeCode()
    ]);
}
```