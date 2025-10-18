# Subscriptions

Subscriptions are the backbone of recurring revenue businesses. This chapter covers everything you need to know about managing subscriptions with the Laravel Stripe
library—from creating simple monthly subscriptions to complex multi-item subscriptions with trials, proration, and billing cycle customization.

## Table of Contents

- [Understanding Subscriptions](#understanding-subscriptions)
- [Basic Subscription Operations](#basic-subscription-operations)
- [Subscription Data Objects](#subscription-data-objects)
- [Subscription Lifecycle](#subscription-lifecycle)
- [Trials and Billing Cycles](#trials-and-billing-cycles)
- [Multi-Item Subscriptions](#multi-item-subscriptions)
- [Proration and Changes](#proration-and-changes)
- [Testing Subscriptions](#testing-subscriptions)
- [Common Patterns](#common-patterns)

## Understanding Subscriptions

Subscriptions represent recurring payments from customers for access to products or services. In Stripe's model:

- **Subscriptions** connect customers to prices with billing automation
- **Subscription Items** represent the specific prices being charged
- **Invoices** are generated automatically based on billing cycles
- **Status** tracks the subscription lifecycle (active, trialing, past_due, canceled, etc.)

```php
// Basic subscription flow
// 1. Customer subscribes to a price
$subscription = Stripe::subscriptions()->create(Stripe::builder()->subscription()->build(
    customer: 'cus_123',
    items: [
        ['price' => 'price_monthly_2999']
    ]
));

// 2. Stripe automatically:
//    - Creates invoices on the billing cycle
//    - Charges the customer's payment method
//    - Handles failed payments and retries
//    - Updates subscription status

// 3. You can query and manage the subscription
$subscription = Stripe::subscriptions()->get('sub_123');
echo $subscription->status->value; // 'active'
```

## Basic Subscription Operations

The subscription service (accessed via `Stripe::subscriptions()`) provides all the methods needed for subscription management.

### Creating Subscriptions

```php
use EncoreDigitalGroup\Stripe\Stripe;

// Simple monthly subscription
$subscription = Stripe::subscriptions()->create(Stripe::builder()->subscription()->build(
    customer: 'cus_customer123',
    items: [
        ['price' => 'price_monthly']
    ]
));

// Subscription with metadata
$subscription = Stripe::subscriptions()->create(Stripe::builder()->subscription()->build(
    customer: 'cus_customer123',
    items: [
        ['price' => 'price_professional']
    ],
    metadata: [
        'user_id' => '12345',
        'plan_name' => 'Professional',
        'signed_up_from' => 'web'
    ]
));

echo $subscription->id; // "sub_abc123..."
echo $subscription->status->value; // "active"
```

### Retrieving Subscriptions

```php
// Get by ID
$subscription = Stripe::subscriptions()->get('sub_abc123');

// Access all properties with full type safety
echo $subscription->customer;              // "cus_123"
echo $subscription->status->value;         // "active"
echo $subscription->currentPeriodStart->toDateTimeString(); // Carbon datetime
echo $subscription->currentPeriodEnd->toDateTimeString();

// Check subscription items
foreach ($subscription->items as $item) {
    echo $item['price'];    // Price ID
    echo $item['quantity']; // Quantity
}
```

### Updating Subscriptions

```php
// Update subscription metadata or settings
$updatedSubscription = Stripe::subscriptions()->update('sub_abc123', Stripe::builder()->subscription()->build(
    description: 'Updated: Professional Plan',
    metadata: [
        'plan_tier' => 'professional_plus',
        'upgraded_at' => now()->toISOString()
    ]
));

// Change items (upgrade/downgrade)
$updatedSubscription = Stripe::subscriptions()->update('sub_abc123', Stripe::builder()->subscription()->build(
    items: [
        ['price' => 'price_premium', 'quantity' => 1]
    ],
    prorationBehavior: ProrationBehavior::CreateProrations
));
```

### Listing Subscriptions

```php
// List all subscriptions
$subscriptions = Stripe::subscriptions()->list();

// Filter by customer
$customerSubscriptions = Stripe::subscriptions()->list([
    'customer' => 'cus_123'
]);

// Filter by status
$activeSubscriptions = Stripe::subscriptions()->list([
    'status' => 'active',
    'limit' => 50
]);

// Work with the collection
$subscriptions->filter(fn($sub) => $sub->status === SubscriptionStatus::Active)
    ->map(fn($sub) => $sub->customer)
    ->unique()
    ->values();
```

### Searching Subscriptions

```php
// Search by metadata
$professionalSubs = Stripe::subscriptions()->search("metadata['plan_tier']:'professional'");

// Search by customer
$customerSubs = Stripe::subscriptions()->search("customer:'cus_123'");

// Combined search
$recentActiveSubs = Stripe::subscriptions()->search(
    "status:'active' AND created>1640995200"
);
```

## Subscription Data Objects

The `StripeSubscription` class provides a strongly-typed representation of subscription data.

### StripeSubscription Properties

```php
use EncoreDigitalGroup\Stripe\Objects\Subscription\StripeSubscription;
use EncoreDigitalGroup\Stripe\Enums\{SubscriptionStatus, CollectionMethod, ProrationBehavior};
use Carbon\CarbonImmutable;

$subscription = Stripe::builder()->subscription()->build(
    id: 'sub_123',                                     // string|null - Subscription ID
    customer: 'cus_123',                               // string|null - Customer ID (required for create)
    status: SubscriptionStatus::Active,                // SubscriptionStatus|null - Current status
    currentPeriodStart: now(),                         // CarbonImmutable|null - Start of current period
    currentPeriodEnd: now()->addMonth(),               // CarbonImmutable|null - End of current period
    cancelAt: null,                                    // CarbonImmutable|null - When to cancel
    canceledAt: null,                                  // CarbonImmutable|null - When canceled
    trialStart: null,                                  // CarbonImmutable|null - Trial period start
    trialEnd: null,                                    // CarbonImmutable|null - Trial period end
    items: [                                           // array|null - Subscription items
        ['price' => 'price_123', 'quantity' => 1]
    ],
    defaultPaymentMethod: 'pm_card',                   // string|null - Payment method ID
    metadata: ['key' => 'value'],                      // array|null - Custom metadata
    currency: 'usd',                                   // string|null - Currency code
    collectionMethod: CollectionMethod::ChargeAutomatically, // CollectionMethod|null
    billingCycleAnchorConfig: null,                    // StripeBillingCycleAnchorConfig|null
    prorationBehavior: ProrationBehavior::CreateProrations, // ProrationBehavior|null
    cancelAtPeriodEnd: false,                          // bool|null - Cancel at end flag
    daysUntilDue: null,                                // int|null - Payment terms
    description: 'Professional subscription'           // string|null - Description
);
```

### Subscription Status Enum

```php
use EncoreDigitalGroup\Stripe\Enums\SubscriptionStatus;

// Available statuses
SubscriptionStatus::Active          // Subscription is active and current
SubscriptionStatus::PastDue         // Payment failed, retrying
SubscriptionStatus::Unpaid          // Payment failed, no retry
SubscriptionStatus::Canceled        // Canceled by customer or business
SubscriptionStatus::Incomplete      // First payment pending
SubscriptionStatus::IncompleteExpired // First payment failed
SubscriptionStatus::Trialing        // In trial period
SubscriptionStatus::Paused          // Temporarily paused

// Usage
if ($subscription->status === SubscriptionStatus::Active) {
    echo "Subscription is active!";
}
```

### Collection Method Enum

```php
use EncoreDigitalGroup\Stripe\Enums\CollectionMethod;

// Charge automatically (most common)
$subscription = Stripe::builder()->subscription()->build(
    customer: 'cus_123',
    items: [['price' => 'price_monthly']],
    collectionMethod: CollectionMethod::ChargeAutomatically
);

// Send invoice (for B2B/NET payment terms)
$subscription = Stripe::builder()->subscription()->build(
    customer: 'cus_b2b',
    items: [['price' => 'price_enterprise']],
    collectionMethod: CollectionMethod::SendInvoice,
    daysUntilDue: 30 // NET 30 payment terms
);
```

### Proration Behavior Enum

```php
use EncoreDigitalGroup\Stripe\Enums\ProrationBehavior;

// Create prorations (default - recommended)
ProrationBehavior::CreateProrations  // Calculate pro-rata charges

// No prorations
ProrationBehavior::None             // No pro-rata adjustment

// Always invoice
ProrationBehavior::AlwaysInvoice    // Create invoice immediately

// Usage when updating
$updated = Stripe::subscriptions()->update('sub_123', Stripe::builder()->subscription()->build(
    items: [['price' => 'price_premium']],
    prorationBehavior: ProrationBehavior::CreateProrations
));
```

## Subscription Lifecycle

Subscriptions move through various states during their lifecycle. The library provides methods to manage these transitions.

### Canceling Subscriptions

```php
// Immediate cancellation
$canceledSubscription = Stripe::subscriptions()->cancelImmediately('sub_abc123');

echo $canceledSubscription->status->value; // "canceled"
echo $canceledSubscription->canceledAt->toDateTimeString(); // When it was canceled

// Cancel at period end (let them finish the billing period)
$subscription = Stripe::subscriptions()->cancelAtPeriodEnd('sub_abc123');

echo $subscription->cancelAtPeriodEnd; // true
echo "Subscription will cancel on: " . $subscription->currentPeriodEnd->toDateString();
```

### Resuming Canceled Subscriptions

```php
// Resume a subscription scheduled for cancellation
$resumedSubscription = Stripe::subscriptions()->resume('sub_abc123');

echo $resumedSubscription->cancelAtPeriodEnd; // false
echo "Subscription will continue billing";
```

### Status Transitions

```php
/**
 * Common subscription lifecycle:
 *
 * 1. incomplete → active (first payment succeeds)
 * 2. active → trialing (starts trial)
 * 3. trialing → active (trial ends)
 * 4. active → past_due (payment fails)
 * 5. past_due → active (payment succeeds after retry)
 * 6. past_due → unpaid/canceled (payment continues to fail)
 * 7. active → canceled (customer or business cancels)
 */

// Handle different statuses
match($subscription->status) {
    SubscriptionStatus::Active => handleActiveSubscription($subscription),
    SubscriptionStatus::Trialing => handleTrialSubscription($subscription),
    SubscriptionStatus::PastDue => handleFailedPayment($subscription),
    SubscriptionStatus::Canceled => handleCancellation($subscription),
    default => logUnexpectedStatus($subscription),
};
```

## Trials and Billing Cycles

### Trial Periods

```php
use Carbon\Carbon;

// Trial with specific end date
$subscription = Stripe::subscriptions()->create(Stripe::builder()->subscription()->build(
    customer: 'cus_123',
    items: [['price' => 'price_monthly']],
    trialEnd: Carbon::now()->addDays(14) // 14-day trial
));

echo $subscription->status->value; // "trialing"
echo $subscription->trialEnd->toDateString(); // Trial end date

// Trial with period from price configuration
// (uses trial_period_days from the price if set)
$subscription = Stripe::subscriptions()->create(Stripe::builder()->subscription()->build(
    customer: 'cus_123',
    items: [['price' => 'price_with_trial']] // Price has trial_period_days: 14
));
```

### Custom Billing Cycle Anchors

Control when billing cycles occur using billing cycle anchor configuration:

```php
use EncoreDigitalGroup\Stripe\Objects\Subscription\StripeBillingCycleAnchorConfig;
use Carbon\Carbon;

// Bill on the 1st of every month
$subscription = Stripe::subscriptions()->create(Stripe::builder()->subscription()->build(
    customer: 'cus_123',
    items: [['price' => 'price_monthly']],
    billingCycleAnchorConfig: Stripe::builder()->subscription()->billingCycleAnchorConfig()->build(
        dayOfMonth: 1
    )
));

// Bill on a specific date and time
$billingDate = Carbon::parse('2025-02-01 09:00:00');
$subscription = Stripe::builder()->subscription()->build(
    customer: 'cus_123',
    items: [['price' => 'price_monthly']]
);

// Using the helper method
$subscription->issueFirstInvoiceOn($billingDate);

Stripe::subscriptions()->create($subscription);
```

### Backdating Subscriptions

```php
// Create a subscription backdated to a specific date
$subscription = Stripe::subscriptions()->create(Stripe::builder()->subscription()->build(
    customer: 'cus_123',
    items: [['price' => 'price_monthly']],
    currentPeriodStart: Carbon::parse('2025-01-01'),
    currentPeriodEnd: Carbon::parse('2025-02-01')
));
```

## Multi-Item Subscriptions

Subscriptions can include multiple prices, allowing complex pricing models.

### Creating Multi-Item Subscriptions

```php
// SaaS with base fee + usage charges
$subscription = Stripe::subscriptions()->create(Stripe::builder()->subscription()->build(
    customer: 'cus_123',
    items: [
        [
            'price' => 'price_base_subscription',
            'quantity' => 1,
            'metadata' => ['type' => 'base_fee']
        ],
        [
            'price' => 'price_api_calls',
            'quantity' => 0, // Metered billing starts at 0
            'metadata' => ['type' => 'usage']
        ],
        [
            'price' => 'price_storage',
            'quantity' => 5, // 5 GB of storage
            'metadata' => ['type' => 'addon']
        ]
    ]
));

// Access subscription items
foreach ($subscription->items as $item) {
    echo "Price: {$item['price']}, Quantity: {$item['quantity']}\n";
}
```

### Managing Subscription Items

```php
// Add an item to existing subscription
$updated = Stripe::subscriptions()->update('sub_123', Stripe::builder()->subscription()->build(
    items: [
        ['price' => 'price_addon_feature', 'quantity' => 1]
    ]
));

// Change quantity of an item
$updated = Stripe::subscriptions()->update('sub_123', Stripe::builder()->subscription()->build(
    items: [
        ['id' => 'si_item123', 'quantity' => 10] // Update specific item
    ]
));

// Remove an item
$updated = Stripe::subscriptions()->update('sub_123', Stripe::builder()->subscription()->build(
    items: [
        ['id' => 'si_item123', 'deleted' => true]
    ]
));
```

## Proration and Changes

### Understanding Proration

When changing subscriptions mid-period, Stripe can calculate pro-rated charges:

```php
use EncoreDigitalGroup\Stripe\Enums\ProrationBehavior;

// Upgrade with proration (customer charged for remainder of period)
$upgraded = Stripe::subscriptions()->update('sub_123', Stripe::builder()->subscription()->build(
    items: [
        ['price' => 'price_premium'] // More expensive plan
    ],
    prorationBehavior: ProrationBehavior::CreateProrations
));

// Immediate upgrade without proration
$upgraded = Stripe::subscriptions()->update('sub_123', Stripe::builder()->subscription()->build(
    items: [
        ['price' => 'price_premium']
    ],
    prorationBehavior: ProrationBehavior::None
));

// Create invoice immediately
$upgraded = Stripe::subscriptions()->update('sub_123', Stripe::builder()->subscription()->build(
    items: [
        ['price' => 'price_premium']
    ],
    prorationBehavior: ProrationBehavior::AlwaysInvoice
));
```

### Scheduled Changes

```php
// Schedule a change for next billing cycle
$subscription = Stripe::subscriptions()->update('sub_123', Stripe::builder()->subscription()->build(
    items: [
        ['price' => 'price_new_plan']
    ],
    prorationBehavior: ProrationBehavior::None, // No immediate charge
    billingCycleAnchorConfig: Stripe::builder()->subscription()->billingCycleAnchorConfig()->build(
        dayOfMonth: 1 // Apply on next 1st of month
    )
));
```

**For complex scheduled changes** (multi-phase pricing, promotional periods, contract-based pricing), consider using **[Subscription Schedules](11-subscription-schedules.md)** which provide more flexibility and control over time-based subscription modifications.

## Testing Subscriptions

The library provides comprehensive testing utilities for subscriptions.

### Basic Subscription Testing

```php
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Support\Testing\{StripeFixtures, StripeMethod};

test('can create a subscription', function () {
    $fake = Stripe::fake([
        StripeMethod::SubscriptionsCreate->value => StripeFixtures::subscription([
            'id' => 'sub_test123',
            'customer' => 'cus_123',
            'status' => 'active'
        ])
    ]);

    $subscription = Stripe::subscriptions()->create(Stripe::builder()->subscription()->build(
        customer: 'cus_123',
        items: [
            ['price' => 'price_monthly']
        ]
    ));

    expect($subscription)
        ->toBeInstanceOf(StripeSubscription::class)
        ->and($subscription->id)->toBe('sub_test123')
        ->and($subscription->status)->toBe(SubscriptionStatus::Active)
        ->and($fake)->toHaveCalledStripeMethod(StripeMethod::SubscriptionsCreate);
});
```

### Testing Cancellations

```php
test('can cancel subscription immediately', function () {
    $fake = Stripe::fake([
        'subscriptions.cancel' => StripeFixtures::subscription([
            'id' => 'sub_123',
            'status' => 'canceled',
            'canceled_at' => time()
        ])
    ]);

    $subscription = Stripe::subscriptions()->cancelImmediately('sub_123');

    expect($subscription->status)->toBe(SubscriptionStatus::Canceled)
        ->and($subscription->canceledAt)->not->toBeNull()
        ->and($fake)->toHaveCalledStripeMethod('subscriptions.cancel');
});

test('can schedule cancellation at period end', function () {
    $fake = Stripe::fake([
        'subscriptions.update' => StripeFixtures::subscription([
            'id' => 'sub_123',
            'cancel_at_period_end' => true
        ])
    ]);

    $subscription = Stripe::subscriptions()->cancelAtPeriodEnd('sub_123');

    expect($subscription->cancelAtPeriodEnd)->toBeTrue()
        ->and($fake)->toHaveCalledStripeMethod('subscriptions.update');
});
```

### Testing Trials

```php
test('creates subscription with trial period', function () {
    $trialEnd = now()->addDays(14)->timestamp;

    $fake = Stripe::fake([
        'subscriptions.create' => StripeFixtures::subscription([
            'id' => 'sub_trial',
            'status' => 'trialing',
            'trial_end' => $trialEnd
        ])
    ]);

    $subscription = Stripe::subscriptions()->create(Stripe::builder()->subscription()->build(
        customer: 'cus_123',
        items: [['price' => 'price_monthly']],
        trialEnd: now()->addDays(14)
    ));

    expect($subscription->status)->toBe(SubscriptionStatus::Trialing)
        ->and($subscription->trialEnd)->not->toBeNull();
});
```

## Common Patterns

Here are real-world patterns for managing subscriptions.

### SaaS Subscription Management

```php
class SubscriptionManager
{
    public function __construct(
        private StripeSubscriptionService $subscriptionService
    ) {}

    public function subscribe(User $user, string $priceId): StripeSubscription
    {
        $subscription = $this->subscriptionService->create(Stripe::builder()->subscription()->build(
            customer: $user->stripe_customer_id,
            items: [
                ['price' => $priceId, 'quantity' => 1]
            ],
            metadata: [
                'user_id' => $user->id,
                'subscribed_at' => now()->toISOString(),
                'plan' => $this->getPlanNameFromPrice($priceId)
            ]
        ));

        // Store subscription ID
        $user->update(['stripe_subscription_id' => $subscription->id]);

        return $subscription;
    }

    public function upgrade(User $user, string $newPriceId): StripeSubscription
    {
        return $this->subscriptionService->update($user->stripe_subscription_id, Stripe::builder()->subscription()->build(
            items: [['price' => $newPriceId]],
            prorationBehavior: ProrationBehavior::CreateProrations,
            metadata: [
                'upgraded_at' => now()->toISOString(),
                'previous_plan' => $this->getCurrentPlan($user),
                'new_plan' => $this->getPlanNameFromPrice($newPriceId)
            ]
        ));
    }

    public function downgrade(User $user, string $newPriceId): StripeSubscription
    {
        // Downgrade at period end (no immediate charge)
        return $this->subscriptionService->update($user->stripe_subscription_id, Stripe::builder()->subscription()->build(
            items: [['price' => $newPriceId]],
            prorationBehavior: ProrationBehavior::None
        ));
    }

    public function cancel(User $user, bool $immediately = false): StripeSubscription
    {
        if ($immediately) {
            return $this->subscriptionService->cancelImmediately($user->stripe_subscription_id);
        }

        return $this->subscriptionService->cancelAtPeriodEnd($user->stripe_subscription_id);
    }
}
```

### Usage-Based Subscriptions

```php
class UsageBasedBillingService
{
    public function createUsageSubscription(User $user): StripeSubscription
    {
        return Stripe::subscriptions()->create(Stripe::builder()->subscription()->build(
            customer: $user->stripe_customer_id,
            items: [
                [
                    'price' => 'price_base_monthly',
                    'quantity' => 1,
                    'metadata' => ['type' => 'base']
                ],
                [
                    'price' => 'price_api_calls_metered',
                    'quantity' => 0, // Metered billing
                    'metadata' => ['type' => 'usage']
                ]
            ]
        ));
    }

    public function recordUsage(User $user, int $quantity): void
    {
        $subscription = Stripe::subscriptions()->get($user->stripe_subscription_id);

        // Find the metered billing item
        $usageItem = collect($subscription->items)
            ->first(fn($item) => $item['metadata']['type'] === 'usage');

        if (!$usageItem) {
            throw new \Exception('No usage item found');
        }

        // Record usage (using Stripe SDK directly for now)
        $client = StripeSubscriptionService::client();
        $client->subscriptionItems->createUsageRecord(
            $usageItem['id'],
            ['quantity' => $quantity, 'timestamp' => time()]
        );
    }
}
```

### Trial Management

```php
class TrialService
{
    public function startTrialSubscription(User $user, string $priceId, int $trialDays = 14): StripeSubscription
    {
        $subscription = Stripe::subscriptions()->create(Stripe::builder()->subscription()->build(
            customer: $user->stripe_customer_id,
            items: [['price' => $priceId]],
            trialEnd: now()->addDays($trialDays),
            metadata: [
                'trial_days' => $trialDays,
                'trial_started_at' => now()->toISOString()
            ]
        ));

        $user->update([
            'stripe_subscription_id' => $subscription->id,
            'trial_ends_at' => $subscription->trialEnd
        ]);

        return $subscription;
    }

    public function extendTrial(User $user, int $additionalDays): StripeSubscription
    {
        $subscription = Stripe::subscriptions()->get($user->stripe_subscription_id);

        $newTrialEnd = $subscription->trialEnd->addDays($additionalDays);

        return Stripe::subscriptions()->update($user->stripe_subscription_id, Stripe::builder()->subscription()->build(
            trialEnd: $newTrialEnd,
            metadata: [
                'trial_extended_at' => now()->toISOString(),
                'additional_days' => $additionalDays
            ]
        ));
    }

    public function endTrialEarly(User $user): StripeSubscription
    {
        return Stripe::subscriptions()->update($user->stripe_subscription_id, Stripe::builder()->subscription()->build(
            trialEnd: now()
        ));
    }
}
```

### Subscription Analytics

```php
class SubscriptionAnalytics
{
    public function getSubscriptionStats(): array
    {
        $subscriptions = Stripe::subscriptions()->list(['limit' => 100]);

        $stats = [
            'total' => $subscriptions->count(),
            'active' => $subscriptions->where('status', SubscriptionStatus::Active)->count(),
            'trialing' => $subscriptions->where('status', SubscriptionStatus::Trialing)->count(),
            'past_due' => $subscriptions->where('status', SubscriptionStatus::PastDue)->count(),
            'canceled' => $subscriptions->where('status', SubscriptionStatus::Canceled)->count(),
        ];

        // Monthly Recurring Revenue (MRR) calculation
        $stats['mrr'] = $this->calculateMRR($subscriptions);

        return $stats;
    }

    private function calculateMRR(Collection $subscriptions): float
    {
        return $subscriptions
            ->filter(fn($sub) => $sub->status === SubscriptionStatus::Active)
            ->sum(function ($sub) {
                $total = 0;
                foreach ($sub->items as $item) {
                    // This is simplified - you'd need to fetch actual price amounts
                    $total += ($item['quantity'] ?? 1);
                }
                return $total;
            });
    }

    public function findChurnRisk(): Collection
    {
        // Find subscriptions at risk of churning
        return Stripe::subscriptions()->search("status:'past_due'");
    }
}
```

## Next Steps

Now that you understand subscription management, you can explore related topics:

- **[Customers](02-customers.md)** - Managing customer data and payment methods
- **[Products](03-products.md)** - Product catalog management
- **[Prices](04-prices.md)** - Complex pricing models
- **[Subscription Schedules](11-subscription-schedules.md)** - Plan complex subscription changes over time
- **[Testing](05-testing.md)** - Comprehensive testing strategies

Or explore advanced features:

- **[Financial Connections](08-financial-connections.md)** - Bank account connections
- **[Webhooks](09-webhooks.md)** - Handling Stripe events
