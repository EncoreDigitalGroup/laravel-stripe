# Prices

Prices are where Stripe's power truly shines. They define how much customers pay, when they pay, and how billing works. This chapter covers everything about price
management in the Laravel Stripe library—from simple one-time payments to complex recurring billing with tiers, usage-based charges, and custom configurations.

## Table of Contents

- [Understanding Stripe's Pricing Model](#understanding-stripes-pricing-model)
- [Basic Price Operations](#basic-price-operations)
- [Price Data Objects](#price-data-objects)
- [Recurring Billing](#recurring-billing)
- [Tiered Pricing](#tiered-pricing)
- [Usage-Based Pricing](#usage-based-pricing)
- [Advanced Features](#advanced-features)
- [Price Lifecycle Management](#price-lifecycle-management)
- [Testing Price Operations](#testing-price-operations)
- [Common Patterns](#common-patterns)

## Understanding Stripe's Pricing Model

Stripe's pricing system is designed around flexibility and scalability:

- **Products** describe what you're selling
- **Prices** define the specific cost and billing model for a product
- One product can have multiple prices (different currencies, billing frequencies, tiers)
- Prices are mostly immutable after creation (for billing consistency)

```php
// Example: SaaS product with multiple pricing models
$product = Stripe::products()->create(Stripe::builder()->product()->build(name: 'Project Management Software'));

// Monthly subscription
$monthlyPrice = Stripe::prices()->create(Stripe::builder()->price()->build(
    product: $product->id,
    unitAmount: 2999,  // $29.99
    currency: 'usd',
    type: PriceType::Recurring,
    recurring: Stripe::builder()->product()->recurring()->build(interval: RecurringInterval::Month)
));

// Annual subscription (with discount)
$annualPrice = Stripe::prices()->create(Stripe::builder()->price()->build(
    product: $product->id,
    unitAmount: 29999, // $299.99 (2 months free)
    currency: 'usd',
    type: PriceType::Recurring,
    recurring: Stripe::builder()->product()->recurring()->build(interval: RecurringInterval::Year)
));

// One-time setup fee
$setupFee = Stripe::prices()->create(Stripe::builder()->price()->build(
    product: $product->id,
    unitAmount: 9999,  // $99.99
    currency: 'usd',
    type: PriceType::OneTime
));
```

## Basic Price Operations

The price service (accessed via `Stripe::prices()`) provides methods for creating, retrieving, and managing prices with special considerations for Stripe's immutability
rules.

### Creating Prices

```php
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Enums\{PriceType, RecurringInterval};

// Simple one-time price
$price = Stripe::prices()->create(Stripe::builder()->price()->build(
    product: 'prod_abc123',
    unitAmount: 1999,  // $19.99 in cents
    currency: 'usd',
    type: PriceType::OneTime
));

// Monthly subscription
$subscription = Stripe::prices()->create(Stripe::builder()->price()->build(
    product: 'prod_abc123',
    unitAmount: 999,   // $9.99/month
    currency: 'usd',
    type: PriceType::Recurring,
    recurring: Stripe::builder()->product()->recurring()->build(
        interval: RecurringInterval::Month,
        intervalCount: 1
    )
));

// With metadata and nickname
$premium = Stripe::prices()->create(Stripe::builder()->price()->build(
    product: 'prod_abc123',
    unitAmount: 4999,
    currency: 'usd',
    type: PriceType::Recurring,
    nickname: 'Premium Monthly',
    metadata: [
        'tier' => 'premium',
        'features' => 'unlimited'
    ],
    recurring: Stripe::builder()->product()->recurring()->build(interval: RecurringInterval::Month)
));

echo $price->id; // "price_abc123..."
```

### Retrieving Prices

```php
// Get by ID
$price = Stripe::prices()->get('price_abc123');

// Access all properties with full type safety
echo $price->unitAmount;    // 1999
echo $price->currency;      // "usd"
echo $price->type->value;   // "one_time"
echo $price->nickname;      // "Premium Monthly"

// Check if it's recurring
if ($price->type === PriceType::Recurring) {
    echo $price->recurring->interval->value; // "month"
    echo $price->recurring->intervalCount;   // 1
}
```

### Updating Prices

**Important**: Prices have very limited update capabilities to preserve billing consistency.

```php
// Only these fields can be updated:
$updatedPrice = Stripe::prices()->update('price_abc123', Stripe::builder()->price()->build(
    active: false,           // Archive/reactivate
    nickname: 'Updated Name', // Display name
    metadata: [              // Custom metadata
        'tier' => 'standard',
        'updated_at' => date('Y-m-d')
    ],
    lookupKey: 'std_monthly', // Lookup identifier
    taxBehavior: TaxBehavior::Exclusive // Tax calculation
));

// These fields CANNOT be updated after creation:
// - product, currency, unitAmount, type, recurring, tiers, etc.
```

### Listing Prices

```php
// All prices
$prices = Stripe::prices()->list();

// Filter by product
$productPrices = Stripe::prices()->listByProduct('prod_abc123');

// Filter by active status
$activePrices = Stripe::prices()->list(['active' => true]);

// Pagination
$prices = Stripe::prices()->list([
    'limit' => 10,
    'starting_after' => 'price_last_id'
]);

// Work with collections
$monthlies = $productPrices->filter(function ($price) {
    return $price->type === PriceType::Recurring &&
           $price->recurring->interval === RecurringInterval::Month;
});
```

### Lookup Keys

Lookup keys provide a convenient way to reference prices by name instead of ID:

```php
// Create with lookup key
$price = Stripe::prices()->create(Stripe::builder()->price()->build(
    product: 'prod_abc123',
    unitAmount: 2999,
    currency: 'usd',
    type: PriceType::Recurring,
    lookupKey: 'premium_monthly',
    recurring: Stripe::builder()->product()->recurring()->build(interval: RecurringInterval::Month)
));

// Retrieve by lookup key
$price = Stripe::prices()->getByLookupKey('premium_monthly');

if ($price) {
    echo "Found price: {$price->id}";
} else {
    echo "Price not found";
}
```

## Price Data Objects

The `StripePrice` class represents complex pricing configurations with full type safety.

### StripePrice Properties

```php
use EncoreDigitalGroup\Stripe\Objects\Product\StripePrice;
use EncoreDigitalGroup\Stripe\Enums\*;

$price = Stripe::builder()->price()->build(
    id: 'price_123',                      // string|null - Stripe price ID
    product: 'prod_123',                  // string|null - Product ID (required for create)
    active: true,                         // bool|null - Whether price is active
    currency: 'usd',                      // string|null - Currency code
    unitAmount: 2999,                     // int|null - Amount in smallest currency unit
    unitAmountDecimal: '29.99',           // string|null - Decimal amount for high precision
    type: PriceType::Recurring,           // PriceType|null - OneTime or Recurring
    billingScheme: BillingScheme::PerUnit, // BillingScheme|null - How to calculate total
    recurring: Stripe::builder()->product()->recurring()->build(...),   // StripeRecurring|null - Recurring billing configuration
    nickname: 'Premium Plan',            // string|null - Display name
    metadata: ['tier' => 'premium'],     // array|null - Custom metadata
    lookupKey: 'premium_monthly',         // string|null - Lookup identifier
    tiers: [...],                        // StripeProductTierCollection|null - Tiered pricing configuration
    tiersMode: TiersMode::Graduated,      // TiersMode|null - How tiers are calculated
    transformQuantity: 100,              // int|null - Quantity transformation divisor
    customUnitAmount: [...],             // StripeCustomUnitAmount|null - Customer-defined pricing
    taxBehavior: TaxBehavior::Exclusive,  // TaxBehavior|null - Tax calculation method
    created: 1640995200                   // int|null - Creation timestamp (read-only)
);
```

### Object Builders

The library provides multiple ways to create objects for consistency and discoverability:

```php
// All DTOs are created using the builder pattern
$tier = Stripe::builder()->product()->tier()->build(upTo: 1000, unitAmount: 100);
$customer = Stripe::builder()->customer()->build(email: 'user@example.com');
$address = Stripe::builder()->address()->build(line1: '123 Main St', city: 'Boston');
$product = Stripe::builder()->product()->build(name: 'My Product');
$price = Stripe::builder()->price()->build(unitAmount: 2999, currency: 'usd');
$recurring = Stripe::builder()->product()->recurring()->build(interval: RecurringInterval::Month);
```

**Available Builder Categories:**

**Main Entity Builders:**

- `Stripe::builder()->customer()->build()` - Customer objects
- `Stripe::builder()->product()->build()` - Product objects
- `Stripe::builder()->price()->build()` - Price objects
- `Stripe::builder()->subscription()->build()` - Subscription objects
- `Stripe::builder()->financialConnection()->build()` - Financial connection objects

**Support Object Builders:**

- `Stripe::builder()->address()->build()` - Address objects
- `Stripe::builder()->shipping()->build()` - Shipping objects
- `Stripe::builder()->webhook()->build()` - Webhook objects

**Sub-Object Builders:**

- `Stripe::builder()->product()->tier()->build()` - Price tier objects
- `Stripe::builder()->product()->customUnitAmount()->build()` - Custom unit amount objects
- `Stripe::builder()->product()->recurring()->build()` - Recurring billing objects
- `Stripe::builder()->financialConnection()->bankAccount()->build()` - Bank account objects
- `Stripe::builder()->financialConnection()->transactionRefresh()->build()` - Transaction refresh objects

**When to use each approach:**

- **Direct DTO creation**: Best for one-off objects or when you know exactly what you need
- **Builder pattern**: Best for discoverability and when building complex nested objects
- **Facade shortcuts**: Best for main Stripe entities (customers, products, prices, subscriptions)

**Complex Builder Examples:**

```php
// Building a customer with address and shipping
$customer = Stripe::builder()->customer()->build(
    email: 'user@example.com',
    name: 'John Doe',
    address: Stripe::builder()->address()->build(
        line1: '123 Main St',
        city: 'Boston',
        state: 'MA',
        postalCode: '02101',
        country: 'US'
    ),
    shipping: Stripe::builder()->shipping()->build(
        name: 'John Doe',
        address: Stripe::builder()->address()->build(
            line1: '456 Oak Ave',
            city: 'Cambridge',
            state: 'MA',
            postalCode: '02138',
            country: 'US'
        )
    )
);

// Building financial connections with bank accounts
$financialConnection = Stripe::builder()->financialConnection()->build(
    accountHolder: [
        'type' => 'individual',
        'individual' => ['first_name' => 'John', 'last_name' => 'Doe']
    ]
);

$bankAccount = Stripe::builder()->financialConnection()->bankAccount()->build(
    country: 'US',
    currency: 'usd',
    accountHolderType: 'individual'
);

// Building webhook configurations
$webhook = Stripe::builder()->webhook()->build(
    url: 'https://api.example.com/webhooks/stripe',
    enabledEvents: ['customer.created', 'payment_intent.succeeded']
);
```

### Working with Enums

The library provides type-safe enums for all Stripe constants:

```php
use EncoreDigitalGroup\Stripe\Enums\*;

// Price types
$oneTime = PriceType::OneTime;
$recurring = PriceType::Recurring;

// Recurring intervals
$monthly = RecurringInterval::Month;
$yearly = RecurringInterval::Year;
$weekly = RecurringInterval::Week;
$daily = RecurringInterval::Day;

// Billing schemes
$perUnit = BillingScheme::PerUnit;      // Standard per-unit billing
$tiered = BillingScheme::Tiered;        // Tiered/volume pricing

// Tax behavior
$inclusive = TaxBehavior::Inclusive;     // Tax included in price
$exclusive = TaxBehavior::Exclusive;     // Tax added to price
$unspecified = TaxBehavior::Unspecified; // No tax calculation

// Tiers modes (for tiered pricing)
$graduated = TiersMode::Graduated;       // Each tier applies to its quantity range
$volume = TiersMode::Volume;             // Entire quantity uses one tier's pricing

// Usage types (for recurring billing)
$licensed = RecurringUsageType::Licensed;  // Fixed quantity
$metered = RecurringUsageType::Metered;    // Variable quantity based on usage

// Aggregate usage (for metered billing)
$sum = RecurringAggregateUsage::Sum;           // Add up usage
$lastDuringPeriod = RecurringAggregateUsage::LastDuringPeriod; // Use last recorded value
$lastEver = RecurringAggregateUsage::LastEver;  // Use most recent value ever
$max = RecurringAggregateUsage::Max;           // Use highest value in period
```

## Recurring Billing

Recurring billing is one of Stripe's most powerful features, supporting complex subscription models.

### Basic Recurring Prices

```php
// Monthly subscription
$monthly = Stripe::prices()->create(Stripe::builder()->price()->build(
    product: 'prod_saas',
    unitAmount: 2999,  // $29.99/month
    currency: 'usd',
    type: PriceType::Recurring,
    recurring: Stripe::builder()->product()->recurring()->build(
        interval: RecurringInterval::Month,
        intervalCount: 1
    )
));

// Quarterly billing
$quarterly = Stripe::prices()->create(Stripe::builder()->price()->build(
    product: 'prod_saas',
    unitAmount: 8499,  // $84.99 every 3 months
    currency: 'usd',
    type: PriceType::Recurring,
    recurring: Stripe::builder()->product()->recurring()->build(
        interval: RecurringInterval::Month,
        intervalCount: 3
    )
));

// Weekly billing
$weekly = Stripe::prices()->create(Stripe::builder()->price()->build(
    product: 'prod_weekly_service',
    unitAmount: 999,   // $9.99/week
    currency: 'usd',
    type: PriceType::Recurring,
    recurring: Stripe::builder()->product()->recurring()->build(
        interval: RecurringInterval::Week,
        intervalCount: 1
    )
));
```

### Recurring with Trial Periods

```php
$withTrial = Stripe::prices()->create(Stripe::builder()->price()->build(
    product: 'prod_premium',
    unitAmount: 4999,
    currency: 'usd',
    type: PriceType::Recurring,
    recurring: Stripe::builder()->product()->recurring()->build(
        interval: RecurringInterval::Month,
        intervalCount: 1,
        trialPeriodDays: 14  // 14-day free trial
    )
));
```

### Metered Billing

For usage-based subscriptions where quantity varies each billing period:

```php
$apiUsage = Stripe::prices()->create(Stripe::builder()->price()->build(
    product: 'prod_api_service',
    unitAmount: 1,     // $0.01 per API call
    currency: 'usd',
    type: PriceType::Recurring,
    billingScheme: BillingScheme::PerUnit,
    recurring: Stripe::builder()->product()->recurring()->build(
        interval: RecurringInterval::Month,
        usageType: RecurringUsageType::Metered,
        aggregateUsage: RecurringAggregateUsage::Sum
    )
));

// Different aggregation methods
$bandwidthUsage = Stripe::prices()->create(Stripe::builder()->price()->build(
    product: 'prod_bandwidth',
    unitAmount: 50,    // $0.50 per GB
    currency: 'usd',
    type: PriceType::Recurring,
    recurring: Stripe::builder()->product()->recurring()->build(
        interval: RecurringInterval::Month,
        usageType: RecurringUsageType::Metered,
        aggregateUsage: RecurringAggregateUsage::Max  // Bill for peak usage
    )
));
```

## Tiered Pricing

Tiered pricing allows different rates based on quantity, perfect for volume discounts.

### Graduated Tiers

Each tier applies to its specific quantity range:

```php
$graduatedPrice = Stripe::prices()->create(Stripe::builder()->price()->build(
    product: 'prod_storage',
    currency: 'usd',
    type: PriceType::Recurring,
    billingScheme: BillingScheme::Tiered,
    tiersMode: TiersMode::Graduated,
    recurring: Stripe::builder()->product()->recurring()->build(interval: RecurringInterval::Month),
    tiers: [
        Stripe::builder()->product()->tier()->build(
            upTo: 1000,        // First 1,000 units
            unitAmount: 100    // $1.00 each
        ),
        Stripe::builder()->product()->tier()->build(
            upTo: 5000,        // Next 4,000 units (1,001-5,000)
            unitAmount: 80     // $0.80 each
        ),
        Stripe::builder()->product()->tier()->build(
            upTo: 'inf',       // All units above 5,000
            unitAmount: 60     // $0.60 each
        )
    ]
));

// Example: 6,000 units = (1,000 × $1.00) + (4,000 × $0.80) + (1,000 × $0.60) = $4,800

// You can also work with the tiers collection after creation
$firstTier = $graduatedPrice->tiers->first();
echo $firstTier->upTo;        // 1000
echo $firstTier->unitAmount;  // 100

// Find the infinite tier
$infiniteTier = $graduatedPrice->tiers->infiniteTier();
echo $infiniteTier->upTo;     // "inf"

// Filter tiers with flat amounts
$flatTiers = $graduatedPrice->tiers->withFlatAmounts();
```

### Volume Tiers

The entire quantity uses one tier's pricing:

```php
$volumePrice = Stripe::prices()->create(Stripe::builder()->price()->build(
    product: 'prod_licenses',
    currency: 'usd',
    type: PriceType::OneTime,
    billingScheme: BillingScheme::Tiered,
    tiersMode: TiersMode::Volume,
    tiers: [
        Stripe::builder()->product()->tier()->build(
            upTo: 10,          // 1-10 licenses
            unitAmount: 10000  // $100 each
        ),
        Stripe::builder()->product()->tier()->build(
            upTo: 50,          // 11-50 licenses
            unitAmount: 8000   // $80 each
        ),
        Stripe::builder()->product()->tier()->build(
            upTo: 'inf',       // 51+ licenses
            unitAmount: 6000   // $60 each
        )
    ]
));

// Example: 25 licenses = 25 × $80 = $2,000 (uses second tier for all units)
```

### Tiers with Flat Fees

```php
$tiersWithFlat = Stripe::prices()->create(Stripe::builder()->price()->build(
    product: 'prod_enterprise',
    currency: 'usd',
    type: PriceType::Recurring,
    billingScheme: BillingScheme::Tiered,
    tiersMode: TiersMode::Graduated,
    recurring: Stripe::builder()->product()->recurring()->build(interval: RecurringInterval::Month),
    tiers: [
        Stripe::builder()->product()->tier()->build(
            upTo: 100,
            flatAmount: 100000,  // $1,000 flat fee for first 100
            unitAmount: 0
        ),
        Stripe::builder()->product()->tier()->build(
            upTo: 'inf',
            unitAmount: 500      // $5.00 per unit above 100
        )
    ]
));
```

## Usage-Based Pricing

Stripe supports sophisticated usage-based models where the final amount depends on actual consumption.

### Simple Metered Billing

```php
$apiCalls = Stripe::prices()->create(Stripe::builder()->price()->build(
    product: 'prod_api',
    unitAmount: 1,     // $0.01 per call
    currency: 'usd',
    type: PriceType::Recurring,
    recurring: Stripe::builder()->product()->recurring()->build(
        interval: RecurringInterval::Month,
        usageType: RecurringUsageType::Metered,
        aggregateUsage: RecurringAggregateUsage::Sum
    )
));
```

### Different Aggregation Methods

```php
// Sum: Add up all usage records
$totalUsage = RecurringAggregateUsage::Sum;

// Last during period: Use the last recorded value in the billing period
$lastSnapshot = RecurringAggregateUsage::LastDuringPeriod;

// Last ever: Use the most recent value regardless of period
$currentValue = RecurringAggregateUsage::LastEver;

// Max: Use the highest value recorded in the period
$peakUsage = RecurringAggregateUsage::Max;

$storagePrice = Stripe::prices()->create(Stripe::builder()->price()->build(
    product: 'prod_storage',
    unitAmount: 10,    // $0.10 per GB
    currency: 'usd',
    type: PriceType::Recurring,
    recurring: Stripe::builder()->product()->recurring()->build(
        interval: RecurringInterval::Month,
        usageType: RecurringUsageType::Metered,
        aggregateUsage: $peakUsage  // Bill for peak storage used
    )
));
```

### Hybrid Models

Combine fixed fees with usage-based charges by creating multiple prices for the same product:

```php
// Base subscription
$basePrice = Stripe::prices()->create(Stripe::builder()->price()->build(
    product: 'prod_hybrid_service',
    unitAmount: 2999,  // $29.99 base fee
    currency: 'usd',
    type: PriceType::Recurring,
    nickname: 'Base Subscription',
    recurring: Stripe::builder()->product()->recurring()->build(
        interval: RecurringInterval::Month,
        usageType: RecurringUsageType::Licensed
    )
));

// Usage charges
$usagePrice = Stripe::prices()->create(Stripe::builder()->price()->build(
    product: 'prod_hybrid_service',
    unitAmount: 10,    // $0.10 per transaction
    currency: 'usd',
    type: PriceType::Recurring,
    nickname: 'Transaction Fees',
    recurring: Stripe::builder()->product()->recurring()->build(
        interval: RecurringInterval::Month,
        usageType: RecurringUsageType::Metered,
        aggregateUsage: RecurringAggregateUsage::Sum
    )
));
```

## Advanced Features

### Custom Unit Amounts

Allow customers to set their own price within defined limits:

```php
$donationPrice = Stripe::prices()->create(Stripe::builder()->price()->build(
    product: 'prod_donation',
    currency: 'usd',
    type: PriceType::OneTime,
    customUnitAmount: Stripe::builder()->product()->customUnitAmount()->build(
        minimum: 500,    // Minimum $5.00
        maximum: 100000, // Maximum $1,000.00
        preset: 2000     // Suggested $20.00
    )
));

// Access the custom unit amount properties
echo $donationPrice->customUnitAmount->minimum;  // 500
echo $donationPrice->customUnitAmount->maximum;  // 100000
echo $donationPrice->customUnitAmount->preset;   // 2000

// All custom unit amounts are created via builder
$customUnit = Stripe::builder()->product()->customUnitAmount()->build(minimum: 500, maximum: 100000, preset: 2000);
```

### Transform Quantity

Useful for fractional units or quantity transformations:

```php
// Price is per 100 units, but sold individually
$wholesalePrice = Stripe::prices()->create(Stripe::builder()->price()->build(
    product: 'prod_wholesale',
    unitAmount: 10000,      // $100 per 100 units
    currency: 'usd',
    type: PriceType::OneTime,
    transformQuantity: 100  // Divide quantity by 100
));

// Customer buys 1 unit, pays $100 / 100 = $1.00
```

### Tax Behavior

Control how taxes are calculated and displayed:

```php
// Tax included in the price
$inclusivePrice = Stripe::prices()->create(Stripe::builder()->price()->build(
    product: 'prod_b2c',
    unitAmount: 1200,  // $12.00 including tax
    currency: 'usd',
    type: PriceType::OneTime,
    taxBehavior: TaxBehavior::Inclusive
));

// Tax added to the price
$exclusivePrice = Stripe::prices()->create(Stripe::builder()->price()->build(
    product: 'prod_b2b',
    unitAmount: 1000,  // $10.00 + tax
    currency: 'usd',
    type: PriceType::OneTime,
    taxBehavior: TaxBehavior::Exclusive
));
```

## Price Lifecycle Management

Like products, prices follow an archive/reactivate pattern rather than hard deletion.

### Archiving Prices

```php
// Archive a price (sets active = false)
$archivedPrice = $service->archive('price_abc123');

echo $archivedPrice->active; // false

// Archived prices:
// - Can't be used for new subscriptions
// - Existing subscriptions continue unchanged
// - Preserved for billing history
// - Can be reactivated if needed
```

### Reactivating Prices

```php
// Reactivate an archived price
$activePrice = $service->reactivate('price_abc123');

echo $activePrice->active; // true
```

### Why Archive Instead of Delete?

```php
// ❌ Prices cannot be deleted in Stripe
// $service->delete('price_abc123');  // This method doesn't exist

// ✅ Archive instead
$service->archive('price_abc123');
```

Stripe doesn't allow price deletion because:

1. **Billing Integrity**: Existing subscriptions and invoices must remain valid
2. **Historical Data**: Financial records need preserved pricing information
3. **Compliance**: Audit trails require immutable pricing history

## Testing Price Operations

The library provides testing utilities for all price scenarios.

### Basic Price Testing

```php
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeFixtures;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeMethod;

test('can create a recurring price', function () {
    $fake = Stripe::fake([
        StripeMethod::PricesCreate->value => StripeFixtures::price([
            'id' => 'price_test123',
            'unit_amount' => 2999,
            'currency' => 'usd',
            'type' => 'recurring',
            'recurring' => [
                'interval' => 'month',
                'interval_count' => 1
            ]
        ])
    ]);

    $price = Stripe::prices()->create(Stripe::builder()->price()->build(
        product: 'prod_test',
        unitAmount: 2999,
        currency: 'usd',
        type: PriceType::Recurring,
        recurring: Stripe::builder()->product()->recurring()->build(
            interval: RecurringInterval::Month,
            intervalCount: 1
        )
    ));

    expect($price)
        ->toBeInstanceOf(StripePrice::class)
        ->and($price->unitAmount)->toBe(2999)
        ->and($price->type)->toBe(PriceType::Recurring)
        ->and($fake)->toHaveCalledStripeMethod(StripeMethod::PricesCreate);
});
```

### Testing Tiered Pricing

```php
test('creates tiered price with graduated tiers', function () {
    $fake = Stripe::fake([
        'prices.create' => StripeFixtures::price([
            'id' => 'price_tiered',
            'billing_scheme' => 'tiered',
            'tiers_mode' => 'graduated',
            'tiers' => [
                ['up_to' => 1000, 'unit_amount' => 100],
                ['up_to' => 'inf', 'unit_amount' => 80]
            ]
        ])
    ]);

    $price = Stripe::prices()->create(Stripe::builder()->price()->build(
        product: 'prod_storage',
        currency: 'usd',
        type: PriceType::Recurring,
        billingScheme: BillingScheme::Tiered,
        tiersMode: TiersMode::Graduated,
        recurring: Stripe::builder()->product()->recurring()->build(interval: RecurringInterval::Month),
        tiers: [
            Stripe::builder()->product()->tier()->build(upTo: 1000, unitAmount: 100),
            Stripe::builder()->product()->tier()->build(upTo: 'inf', unitAmount: 80)
        ]
    ));

    expect($price->billingScheme)->toBe(BillingScheme::Tiered)
        ->and($price->tiersMode)->toBe(TiersMode::Graduated)
        ->and($price->tiers)->toHaveCount(2);
});
```

### Testing Lookup Keys

```php
test('can retrieve price by lookup key', function () {
    $fake = Stripe::fake([
        'prices.all' => StripeFixtures::priceList([
            StripeFixtures::price([
                'id' => 'price_premium',
                'lookup_key' => 'premium_monthly'
            ])
        ])
    ]);

    $price = Stripe::prices()->getByLookupKey('premium_monthly');

    expect($price)->not->toBeNull()
        ->and($price->lookupKey)->toBe('premium_monthly');

    $params = $fake->getCall('prices.all');
    expect($params['lookup_keys'])->toBe(['premium_monthly']);
});
```

### Testing Update Restrictions

```php
test('update removes immutable fields from payload', function () {
    $fake = Stripe::fake([
        'prices.update' => StripeFixtures::price(['id' => 'price_123'])
    ]);

    $price = Stripe::builder()->price()->build(
        unitAmount: 9999,          // Should be removed
        currency: 'eur',           // Should be removed
        nickname: 'Updated Name',  // Should remain
        active: false              // Should remain
    );

    Stripe::prices()->update('price_123', $price);

    $params = $fake->getCall('prices.update');

    expect($params)
        ->not->toHaveKey('unit_amount')
        ->not->toHaveKey('currency')
        ->toHaveKey('nickname', 'Updated Name')
        ->toHaveKey('active', false);
});
```

## Common Patterns

Here are real-world patterns for implementing pricing strategies.

### SaaS Pricing Tiers

```php
class SaaSPricingService
{
    public function __construct()
    {}

    public function createPricingTiers(string $productId): array
    {
        return [
            'starter' => Stripe::prices()->create(Stripe::builder()->price()->build(
                product: $productId,
                unitAmount: 999,   // $9.99/month
                currency: 'usd',
                type: PriceType::Recurring,
                nickname: 'Starter Plan',
                lookupKey: 'starter_monthly',
                metadata: [
                    'tier' => 'starter',
                    'users' => '5',
                    'storage_gb' => '10'
                ],
                recurring: Stripe::builder()->product()->recurring()->build(
                    interval: RecurringInterval::Month,
                    trialPeriodDays: 14
                )
            )),

            'professional' => Stripe::prices()->create(Stripe::builder()->price()->build(
                product: $productId,
                unitAmount: 2999,  // $29.99/month
                currency: 'usd',
                type: PriceType::Recurring,
                nickname: 'Professional Plan',
                lookupKey: 'professional_monthly',
                metadata: [
                    'tier' => 'professional',
                    'users' => '25',
                    'storage_gb' => '100'
                ],
                recurring: Stripe::builder()->product()->recurring()->build(
                    interval: RecurringInterval::Month,
                    trialPeriodDays: 14
                )
            )),

            'enterprise' => Stripe::prices()->create(Stripe::builder()->price()->build(
                product: $productId,
                unitAmount: 9999,  // $99.99/month
                currency: 'usd',
                type: PriceType::Recurring,
                nickname: 'Enterprise Plan',
                lookupKey: 'enterprise_monthly',
                metadata: [
                    'tier' => 'enterprise',
                    'users' => 'unlimited',
                    'storage_gb' => '1000'
                ],
                recurring: Stripe::builder()->product()->recurring()->build(interval: RecurringInterval::Month)
            ))
        ];
    }

    public function createAnnualDiscounts(array $monthlyPrices): array
    {
        $annualPrices = [];

        foreach ($monthlyPrices as $tier => $monthlyPrice) {
            $annualAmount = $monthlyPrice->unitAmount * 10; // 2 months free

            $annualPrices[$tier . '_annual'] = Stripe::prices()->create(Stripe::builder()->price()->build(
                product: $monthlyPrice->product,
                unitAmount: $annualAmount,
                currency: $monthlyPrice->currency,
                type: PriceType::Recurring,
                nickname: $monthlyPrice->nickname . ' (Annual)',
                lookupKey: str_replace('monthly', 'annual', $monthlyPrice->lookupKey),
                metadata: array_merge($monthlyPrice->metadata, [
                    'billing' => 'annual',
                    'discount' => '17%'
                ]),
                recurring: Stripe::builder()->product()->recurring()->build(interval: RecurringInterval::Year)
            ));
        }

        return $annualPrices;
    }
}
```

### Usage-Based API Pricing

```php
class ApiPricingService
{
    public function createApiPricing(string $productId): array
    {
        return [
            // Base plan with included requests
            'base' => Stripe::prices()->create(Stripe::builder()->price()->build(
                product: $productId,
                unitAmount: 2999,  // $29.99/month base
                currency: 'usd',
                type: PriceType::Recurring,
                nickname: 'API Base Plan',
                lookupKey: 'api_base',
                metadata: [
                    'included_requests' => '10000',
                    'overage_rate' => '0.001'
                ],
                recurring: Stripe::builder()->product()->recurring()->build(
                    interval: RecurringInterval::Month,
                    usageType: RecurringUsageType::Licensed
                )
            )),

            // Overage charges
            'overage' => Stripe::prices()->create(Stripe::builder()->price()->build(
                product: $productId,
                unitAmount: 1,     // $0.01 per extra request
                currency: 'usd',
                type: PriceType::Recurring,
                nickname: 'API Overage',
                lookupKey: 'api_overage',
                recurring: Stripe::builder()->product()->recurring()->build(
                    interval: RecurringInterval::Month,
                    usageType: RecurringUsageType::Metered,
                    aggregateUsage: RecurringAggregateUsage::Sum
                )
            )),

            // Pay-as-you-go option
            'payg' => Stripe::prices()->create(Stripe::builder()->price()->build(
                product: $productId,
                unitAmount: 2,     // $0.02 per request (higher rate)
                currency: 'usd',
                type: PriceType::Recurring,
                nickname: 'Pay As You Go',
                lookupKey: 'api_payg',
                recurring: Stripe::builder()->product()->recurring()->build(
                    interval: RecurringInterval::Month,
                    usageType: RecurringUsageType::Metered,
                    aggregateUsage: RecurringAggregateUsage::Sum
                )
            ))
        ];
    }
}
```

### E-commerce with Volume Discounts

```php
class EcommercePricingService
{
    public function createVolumeDiscounts(string $productId): StripePrice
    {
        return Stripe::prices()->create(Stripe::builder()->price()->build(
            product: $productId,
            currency: 'usd',
            type: PriceType::OneTime,
            nickname: 'Bulk Licensing',
            billingScheme: BillingScheme::Tiered,
            tiersMode: TiersMode::Volume,
            metadata: [
                'pricing_model' => 'volume_discount',
                'break_points' => '1,10,50,100'
            ],
            tiers: [
                Stripe::builder()->product()->tier()->build(
                    upTo: 9,
                    unitAmount: 10000  // $100 each (1-9 licenses)
                ),
                Stripe::builder()->product()->tier()->build(
                    upTo: 49,
                    unitAmount: 9000   // $90 each (10-49 licenses)
                ),
                Stripe::builder()->product()->tier()->build(
                    upTo: 99,
                    unitAmount: 8000   // $80 each (50-99 licenses)
                ),
                Stripe::builder()->product()->tier()->build(
                    upTo: 'inf',
                    unitAmount: 7000   // $70 each (100+ licenses)
                )
            ]
        ));
    }

    public function createGraduatedPricing(string $productId): StripePrice
    {
        return Stripe::prices()->create(Stripe::builder()->price()->build(
            product: $productId,
            currency: 'usd',
            type: PriceType::Recurring,
            nickname: 'Storage with Graduated Pricing',
            billingScheme: BillingScheme::Tiered,
            tiersMode: TiersMode::Graduated,
            recurring: Stripe::builder()->product()->recurring()->build(interval: RecurringInterval::Month),
            tiers: [
                Stripe::builder()->product()->tier()->build(
                    upTo: 100,
                    flatAmount: 1000,   // $10 for first 100 GB
                    unitAmount: 0
                ),
                Stripe::builder()->product()->tier()->build(
                    upTo: 1000,
                    unitAmount: 8       // $0.08/GB for next 900 GB
                ),
                Stripe::builder()->product()->tier()->build(
                    upTo: 'inf',
                    unitAmount: 5       // $0.05/GB for everything above 1TB
                )
            ]
        ));
    }
}
```

### Price Management Service

```php
class PriceManagementService
{
    public function migratePricing(string $oldPriceId, StripePrice $newPriceData): StripePrice
    {
        // Create new price
        $newPrice = Stripe::prices()->create($newPriceData);

        // Archive old price
        Stripe::prices()->archive($oldPriceId);

        // Log the migration
        logger()->info('Price migration completed', [
            'old_price_id' => $oldPriceId,
            'new_price_id' => $newPrice->id,
            'migration_date' => now()
        ]);

        return $newPrice;
    }

    public function analyzeProductPricing(string $productId): array
    {
        $prices = Stripe::prices()->listByProduct($productId);

        $analysis = [
            'total_prices' => $prices->count(),
            'active_prices' => $prices->where('active', true)->count(),
            'archived_prices' => $prices->where('active', false)->count(),
            'price_types' => [],
            'currencies' => [],
            'billing_models' => []
        ];

        foreach ($prices as $price) {
            // Price types
            $type = $price->type->value;
            $analysis['price_types'][$type] = ($analysis['price_types'][$type] ?? 0) + 1;

            // Currencies
            $currency = $price->currency;
            $analysis['currencies'][$currency] = ($analysis['currencies'][$currency] ?? 0) + 1;

            // Billing models
            if ($price->type === PriceType::Recurring) {
                $interval = $price->recurring->interval->value;
                $analysis['billing_models'][$interval] = ($analysis['billing_models'][$interval] ?? 0) + 1;
            }
        }

        return $analysis;
    }

    public function findPricesNeedingAttention(): Collection
    {
        $allPrices = Stripe::prices()->list(['limit' => 100]);

        return $allPrices->filter(function ($price) {
            // Prices without nicknames
            if (empty($price->nickname)) {
                return true;
            }

            // Prices without lookup keys
            if (empty($price->lookupKey)) {
                return true;
            }

            // Archived prices that might need cleanup
            if (!$price->active) {
                return true;
            }

            return false;
        });
    }
}
```

## Next Steps

Now that you understand pricing, you can explore:

- **[Testing](05-testing.md)** - Comprehensive testing strategies for complex pricing scenarios
- **[Architecture](06-architecture.md)** - Deep dive into how the library handles pricing complexity

Or go back to other components:

- **[Customers](02-customers.md)** - Customer management
- **[Products](03-products.md)** - Product lifecycle management