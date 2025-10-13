# Quick Start Guide

This guide will walk you through common Stripe operations using the library. All operations use the `Stripe` class as the single entry point.

## Creating Your First Customer

```php
use EncoreDigitalGroup\Common\Stripe\Stripe;

// Create a customer object
$customerData = Stripe::customer(
    email: 'customer@example.com',
    name: 'Jane Smith',
    phone: '+1-123-456-7890'
);

// Save to Stripe
$customer = Stripe::customers()->create($customerData);

// Access the customer ID
echo $customer->id; // cus_...
```

## Creating Products and Prices

### Create a Product

```php
$productData = Stripe::product(
    name: 'Premium Membership',
    description: 'Access to all premium features'
);

$product = Stripe::products()->create($productData);
```

### Create a Price for the Product

```php
$priceData = Stripe::price(
    product: $product->id,
    unitAmount: 2999, // $29.99 in cents
    currency: 'usd'
);

$price = Stripe::prices()->create($priceData);
```

### Create a Recurring Price

```php
$monthlyPrice = Stripe::price(
    product: $product->id,
    unitAmount: 999, // $9.99/month
    currency: 'usd',
    recurring: [
        'interval' => RecurringInterval::Month,
        'interval_count' => 1
    ]
);

$price = Stripe::prices()->create($monthlyPrice);
```

## Creating a Subscription

```php
$subscriptionData = Stripe::subscription(
    customer: $customer->id,
    items: [
        [
            'price' => $price->id,
            'quantity' => 1
        ]
    ]
);

$subscription = Stripe::subscriptions()->create($subscriptionData);
```

## Retrieving Objects

All services follow the same pattern for retrieval:

```php
// Retrieve by ID
$customer = Stripe::customers()->get('cus_...');
$product = Stripe::products()->get('prod_...');
$price = Stripe::prices()->get('price_...');
$subscription = Stripe::subscriptions()->get('sub_...');
```

## Updating Objects

```php
// Update customer information
$updatedData = Stripe::customer(
    name: 'Jane Doe', // Changed name
    phone: '+1-555-9999' // Updated phone
);

$customer = Stripe::customers()->update('cus_...', $updatedData);
```

## Listing Objects

```php
// List customers
$customers = Stripe::customers()->list(['limit' => 10]);

// List products
$products = Stripe::products()->list();

// List prices for a specific product
$prices = Stripe::prices()->listByProduct('prod_...');
```

## Common Patterns

### Archive vs Delete

Some Stripe objects can be archived (soft delete) rather than deleted:

```php
// Archive a product (sets active = false)
$product = Stripe::products()->archive('prod_...');

// Reactivate a product
$product = Stripe::products()->reactivate('prod_...');

// Prices can only be archived, not deleted
$price = Stripe::prices()->archive('price_...');
```

### Cancel Subscriptions

```php
// Cancel immediately
$subscription = Stripe::subscriptions()->cancel('sub_...');

// Cancel at end of billing period
$subscription = Stripe::subscriptions()->cancelAtPeriodEnd('sub_...');

// Resume a subscription marked for cancellation
$subscription = Stripe::subscriptions()->resume('sub_...');
```

## Working with Type Safety

The library uses enums for constants to prevent magic strings:

```php
use EncoreDigitalGroup\Common\Stripe\Enums\RecurringInterval;
use EncoreDigitalGroup\Common\Stripe\Enums\SubscriptionStatus;

// Use enums instead of strings
$price = Stripe::price(
    product: $product->id,
    unitAmount: 1000,
    currency: 'usd',
    recurring: [
        'interval' => RecurringInterval::Year, // Not 'year'
    ]
);

// Check subscription status with type safety
if ($subscription->status === SubscriptionStatus::Active) {
    // Subscription is active
}
```

## Error Handling

All Stripe API errors are thrown as exceptions:

```php
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;

try {
    $customer = Stripe::customers()->create($customerData);
} catch (ApiErrorException $e) {
    // Handle Stripe API errors
    Log::error('Stripe error: ' . $e->getMessage());
}
```

## Next Steps

- [Architecture Overview](architecture.md) - Understand the library's design
- [Type Safety Guide](type-safety.md) - Learn about DTOs and enums
- [Testing Guide](testing.md) - Test your Stripe integrations
- [API Reference](api/stripe-facade.md) - Complete API documentation
