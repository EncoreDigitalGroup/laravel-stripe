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
$product = Stripe::products()->create(Stripe::product(name: 'Project Management Software'));

// Monthly subscription
$monthlyPrice = Stripe::prices()->create(Stripe::price(
    product: $product->id,
    unitAmount: 2999,  // $29.99
    currency: 'usd',
    type: PriceType::Recurring,
    recurring: ['interval' => RecurringInterval::Month]
));

// Annual subscription (with discount)
$annualPrice = Stripe::prices()->create(Stripe::price(
    product: $product->id,
    unitAmount: 29999, // $299.99 (2 months free)
    currency: 'usd',
    type: PriceType::Recurring,
    recurring: ['interval' => RecurringInterval::Year]
));

// One-time setup fee
$setupFee = Stripe::prices()->create(Stripe::price(
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
$price = Stripe::prices()->create(Stripe::price(
    product: 'prod_abc123',
    unitAmount: 1999,  // $19.99 in cents
    currency: 'usd',
    type: PriceType::OneTime
));

// Monthly subscription
$subscription = Stripe::prices()->create(Stripe::price(
    product: 'prod_abc123',
    unitAmount: 999,   // $9.99/month
    currency: 'usd',
    type: PriceType::Recurring,
    recurring: [
        'interval' => RecurringInterval::Month,
        'interval_count' => 1
    ]
));

// With metadata and nickname
$premium = Stripe::prices()->create(Stripe::price(
    product: 'prod_abc123',
    unitAmount: 4999,
    currency: 'usd',
    type: PriceType::Recurring,
    nickname: 'Premium Monthly',
    metadata: [
        'tier' => 'premium',
        'features' => 'unlimited'
    ],
    recurring: [
        'interval' => RecurringInterval::Month
    ]
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
    echo $price->recurring['interval']->value; // "month"
    echo $price->recurring['interval_count'];  // 1
}
```

### Updating Prices

**Important**: Prices have very limited update capabilities to preserve billing consistency.

```php
// Only these fields can be updated:
$updatedPrice = Stripe::prices()->update('price_abc123', Stripe::price(
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
           $price->recurring['interval'] === RecurringInterval::Month;
});
```

### Lookup Keys

Lookup keys provide a convenient way to reference prices by name instead of ID:

```php
// Create with lookup key
$price = Stripe::prices()->create(Stripe::price(
    product: 'prod_abc123',
    unitAmount: 2999,
    currency: 'usd',
    type: PriceType::Recurring,
    lookupKey: 'premium_monthly',
    recurring: ['interval' => RecurringInterval::Month]
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

$price = Stripe::price(
    id: 'price_123',                      // string|null - Stripe price ID
    product: 'prod_123',                  // string|null - Product ID (required for create)
    active: true,                         // bool|null - Whether price is active
    currency: 'usd',                      // string|null - Currency code
    unitAmount: 2999,                     // int|null - Amount in smallest currency unit
    unitAmountDecimal: '29.99',           // string|null - Decimal amount for high precision
    type: PriceType::Recurring,           // PriceType|null - OneTime or Recurring
    billingScheme: BillingScheme::PerUnit, // BillingScheme|null - How to calculate total
    recurring: [...],                     // array|null - Recurring billing configuration
    nickname: 'Premium Plan',            // string|null - Display name
    metadata: ['tier' => 'premium'],     // array|null - Custom metadata
    lookupKey: 'premium_monthly',         // string|null - Lookup identifier
    tiers: [...],                        // array|null - Tiered pricing configuration
    tiersMode: TiersMode::Graduated,      // TiersMode|null - How tiers are calculated
    transformQuantity: 100,              // int|null - Quantity transformation divisor
    customUnitAmount: [...],             // array|null - Customer-defined pricing
    taxBehavior: TaxBehavior::Exclusive,  // TaxBehavior|null - Tax calculation method
    created: 1640995200                   // int|null - Creation timestamp (read-only)
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
$monthly = Stripe::prices()->create(Stripe::price(
    product: 'prod_saas',
    unitAmount: 2999,  // $29.99/month
    currency: 'usd',
    type: PriceType::Recurring,
    recurring: [
        'interval' => RecurringInterval::Month,
        'interval_count' => 1
    ]
));

// Quarterly billing
$quarterly = Stripe::prices()->create(Stripe::price(
    product: 'prod_saas',
    unitAmount: 8499,  // $84.99 every 3 months
    currency: 'usd',
    type: PriceType::Recurring,
    recurring: [
        'interval' => RecurringInterval::Month,
        'interval_count' => 3
    ]
));

// Weekly billing
$weekly = Stripe::prices()->create(Stripe::price(
    product: 'prod_weekly_service',
    unitAmount: 999,   // $9.99/week
    currency: 'usd',
    type: PriceType::Recurring,
    recurring: [
        'interval' => RecurringInterval::Week,
        'interval_count' => 1
    ]
));
```

### Recurring with Trial Periods

```php
$withTrial = $service->create(StripePrice::make(
    product: 'prod_premium',
    unitAmount: 4999,
    currency: 'usd',
    type: PriceType::Recurring,
    recurring: [
        'interval' => RecurringInterval::Month,
        'interval_count' => 1,
        'trial_period_days' => 14  // 14-day free trial
    ]
));
```

### Metered Billing

For usage-based subscriptions where quantity varies each billing period:

```php
$apiUsage = $service->create(StripePrice::make(
    product: 'prod_api_service',
    unitAmount: 1,     // $0.01 per API call
    currency: 'usd',
    type: PriceType::Recurring,
    billingScheme: BillingScheme::PerUnit,
    recurring: [
        'interval' => RecurringInterval::Month,
        'usage_type' => RecurringUsageType::Metered,
        'aggregate_usage' => RecurringAggregateUsage::Sum
    ]
));

// Different aggregation methods
$bandwidthUsage = $service->create(StripePrice::make(
    product: 'prod_bandwidth',
    unitAmount: 50,    // $0.50 per GB
    currency: 'usd',
    type: PriceType::Recurring,
    recurring: [
        'interval' => RecurringInterval::Month,
        'usage_type' => RecurringUsageType::Metered,
        'aggregate_usage' => RecurringAggregateUsage::Max  // Bill for peak usage
    ]
));
```

## Tiered Pricing

Tiered pricing allows different rates based on quantity, perfect for volume discounts.

### Graduated Tiers

Each tier applies to its specific quantity range:

```php
$graduatedPrice = $service->create(StripePrice::make(
    product: 'prod_storage',
    currency: 'usd',
    type: PriceType::Recurring,
    billingScheme: BillingScheme::Tiered,
    tiersMode: TiersMode::Graduated,
    recurring: [
        'interval' => RecurringInterval::Month
    ],
    tiers: [
        [
            'up_to' => 1000,        // First 1,000 units
            'unit_amount' => 100    // $1.00 each
        ],
        [
            'up_to' => 5000,        // Next 4,000 units (1,001-5,000)
            'unit_amount' => 80     // $0.80 each
        ],
        [
            'up_to' => 'inf',       // All units above 5,000
            'unit_amount' => 60     // $0.60 each
        ]
    ]
));

// Example: 6,000 units = (1,000 × $1.00) + (4,000 × $0.80) + (1,000 × $0.60) = $4,800
```

### Volume Tiers

The entire quantity uses one tier's pricing:

```php
$volumePrice = $service->create(StripePrice::make(
    product: 'prod_licenses',
    currency: 'usd',
    type: PriceType::OneTime,
    billingScheme: BillingScheme::Tiered,
    tiersMode: TiersMode::Volume,
    tiers: [
        [
            'up_to' => 10,          // 1-10 licenses
            'unit_amount' => 10000  // $100 each
        ],
        [
            'up_to' => 50,          // 11-50 licenses
            'unit_amount' => 8000   // $80 each
        ],
        [
            'up_to' => 'inf',       // 51+ licenses
            'unit_amount' => 6000   // $60 each
        ]
    ]
));

// Example: 25 licenses = 25 × $80 = $2,000 (uses second tier for all units)
```

### Tiers with Flat Fees

```php
$tiersWithFlat = $service->create(StripePrice::make(
    product: 'prod_enterprise',
    currency: 'usd',
    type: PriceType::Recurring,
    billingScheme: BillingScheme::Tiered,
    tiersMode: TiersMode::Graduated,
    recurring: ['interval' => RecurringInterval::Month],
    tiers: [
        [
            'up_to' => 100,
            'flat_amount' => 100000,  // $1,000 flat fee for first 100
            'unit_amount' => 0
        ],
        [
            'up_to' => 'inf',
            'unit_amount' => 500      // $5.00 per unit above 100
        ]
    ]
));
```

## Usage-Based Pricing

Stripe supports sophisticated usage-based models where the final amount depends on actual consumption.

### Simple Metered Billing

```php
$apiCalls = $service->create(StripePrice::make(
    product: 'prod_api',
    unitAmount: 1,     // $0.01 per call
    currency: 'usd',
    type: PriceType::Recurring,
    recurring: [
        'interval' => RecurringInterval::Month,
        'usage_type' => RecurringUsageType::Metered,
        'aggregate_usage' => RecurringAggregateUsage::Sum
    ]
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

$storagePrice = $service->create(StripePrice::make(
    product: 'prod_storage',
    unitAmount: 10,    // $0.10 per GB
    currency: 'usd',
    type: PriceType::Recurring,
    recurring: [
        'interval' => RecurringInterval::Month,
        'usage_type' => RecurringUsageType::Metered,
        'aggregate_usage' => $peakUsage  // Bill for peak storage used
    ]
));
```

### Hybrid Models

Combine fixed fees with usage-based charges by creating multiple prices for the same product:

```php
// Base subscription
$basePrice = $service->create(StripePrice::make(
    product: 'prod_hybrid_service',
    unitAmount: 2999,  // $29.99 base fee
    currency: 'usd',
    type: PriceType::Recurring,
    nickname: 'Base Subscription',
    recurring: [
        'interval' => RecurringInterval::Month,
        'usage_type' => RecurringUsageType::Licensed
    ]
));

// Usage charges
$usagePrice = $service->create(StripePrice::make(
    product: 'prod_hybrid_service',
    unitAmount: 10,    // $0.10 per transaction
    currency: 'usd',
    type: PriceType::Recurring,
    nickname: 'Transaction Fees',
    recurring: [
        'interval' => RecurringInterval::Month,
        'usage_type' => RecurringUsageType::Metered,
        'aggregate_usage' => RecurringAggregateUsage::Sum
    ]
));
```

## Advanced Features

### Custom Unit Amounts

Allow customers to set their own price within defined limits:

```php
$donationPrice = $service->create(StripePrice::make(
    product: 'prod_donation',
    currency: 'usd',
    type: PriceType::OneTime,
    customUnitAmount: [
        'minimum' => 500,    // Minimum $5.00
        'maximum' => 100000, // Maximum $1,000.00
        'preset' => 2000     // Suggested $20.00
    ]
));
```

### Transform Quantity

Useful for fractional units or quantity transformations:

```php
// Price is per 100 units, but sold individually
$wholesalePrice = $service->create(StripePrice::make(
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
$inclusivePrice = $service->create(StripePrice::make(
    product: 'prod_b2c',
    unitAmount: 1200,  // $12.00 including tax
    currency: 'usd',
    type: PriceType::OneTime,
    taxBehavior: TaxBehavior::Inclusive
));

// Tax added to the price
$exclusivePrice = $service->create(StripePrice::make(
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

    $price = Stripe::prices()->create(Stripe::price(
        product: 'prod_test',
        unitAmount: 2999,
        currency: 'usd',
        type: PriceType::Recurring,
        recurring: [
            'interval' => RecurringInterval::Month,
            'interval_count' => 1
        ]
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

    $service = StripePriceService::make();
    $price = $service->create(StripePrice::make(
        product: 'prod_storage',
        currency: 'usd',
        type: PriceType::Recurring,
        billingScheme: BillingScheme::Tiered,
        tiersMode: TiersMode::Graduated,
        recurring: ['interval' => RecurringInterval::Month],
        tiers: [
            ['up_to' => 1000, 'unit_amount' => 100],
            ['up_to' => 'inf', 'unit_amount' => 80]
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

    $price = Stripe::price(
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
    public function __construct(
        private StripePriceService $priceService,
        private StripeProductService $productService
    ) {}

    public function createPricingTiers(string $productId): array
    {
        return [
            'starter' => $this->priceService->create(StripePrice::make(
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
                recurring: [
                    'interval' => RecurringInterval::Month,
                    'trial_period_days' => 14
                ]
            )),

            'professional' => $this->priceService->create(StripePrice::make(
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
                recurring: [
                    'interval' => RecurringInterval::Month,
                    'trial_period_days' => 14
                ]
            )),

            'enterprise' => $this->priceService->create(StripePrice::make(
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
                recurring: [
                    'interval' => RecurringInterval::Month
                ]
            ))
        ];
    }

    public function createAnnualDiscounts(array $monthlyPrices): array
    {
        $annualPrices = [];

        foreach ($monthlyPrices as $tier => $monthlyPrice) {
            $annualAmount = $monthlyPrice->unitAmount * 10; // 2 months free

            $annualPrices[$tier . '_annual'] = $this->priceService->create(StripePrice::make(
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
                recurring: [
                    'interval' => RecurringInterval::Year
                ]
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
            'base' => $this->priceService->create(StripePrice::make(
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
                recurring: [
                    'interval' => RecurringInterval::Month,
                    'usage_type' => RecurringUsageType::Licensed
                ]
            )),

            // Overage charges
            'overage' => $this->priceService->create(StripePrice::make(
                product: $productId,
                unitAmount: 1,     // $0.01 per extra request
                currency: 'usd',
                type: PriceType::Recurring,
                nickname: 'API Overage',
                lookupKey: 'api_overage',
                recurring: [
                    'interval' => RecurringInterval::Month,
                    'usage_type' => RecurringUsageType::Metered,
                    'aggregate_usage' => RecurringAggregateUsage::Sum
                ]
            )),

            // Pay-as-you-go option
            'payg' => $this->priceService->create(StripePrice::make(
                product: $productId,
                unitAmount: 2,     // $0.02 per request (higher rate)
                currency: 'usd',
                type: PriceType::Recurring,
                nickname: 'Pay As You Go',
                lookupKey: 'api_payg',
                recurring: [
                    'interval' => RecurringInterval::Month,
                    'usage_type' => RecurringUsageType::Metered,
                    'aggregate_usage' => RecurringAggregateUsage::Sum
                ]
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
        return $this->priceService->create(StripePrice::make(
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
                [
                    'up_to' => 9,
                    'unit_amount' => 10000  // $100 each (1-9 licenses)
                ],
                [
                    'up_to' => 49,
                    'unit_amount' => 9000   // $90 each (10-49 licenses)
                ],
                [
                    'up_to' => 99,
                    'unit_amount' => 8000   // $80 each (50-99 licenses)
                ],
                [
                    'up_to' => 'inf',
                    'unit_amount' => 7000   // $70 each (100+ licenses)
                ]
            ]
        ));
    }

    public function createGraduatedPricing(string $productId): StripePrice
    {
        return $this->priceService->create(StripePrice::make(
            product: $productId,
            currency: 'usd',
            type: PriceType::Recurring,
            nickname: 'Storage with Graduated Pricing',
            billingScheme: BillingScheme::Tiered,
            tiersMode: TiersMode::Graduated,
            recurring: ['interval' => RecurringInterval::Month],
            tiers: [
                [
                    'up_to' => 100,
                    'flat_amount' => 1000,   // $10 for first 100 GB
                    'unit_amount' => 0
                ],
                [
                    'up_to' => 1000,
                    'unit_amount' => 8       // $0.08/GB for next 900 GB
                ],
                [
                    'up_to' => 'inf',
                    'unit_amount' => 5       // $0.05/GB for everything above 1TB
                ]
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
        $newPrice = $this->priceService->create($newPriceData);

        // Archive old price
        $this->priceService->archive($oldPriceId);

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
        $prices = $this->priceService->listByProduct($productId);

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
                $interval = $price->recurring['interval']->value;
                $analysis['billing_models'][$interval] = ($analysis['billing_models'][$interval] ?? 0) + 1;
            }
        }

        return $analysis;
    }

    public function findPricesNeedingAttention(): Collection
    {
        $allPrices = $this->priceService->list(['limit' => 100]);

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