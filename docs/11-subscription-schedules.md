# Subscription Schedules

Subscription schedules allow you to create, modify, and manage complex subscription changes over time. They provide a way to plan subscription modifications—such as price
changes, plan upgrades, or temporary discounts—that take effect at specific dates in the future.

## Table of Contents

- [Understanding Subscription Schedules](#understanding-subscription-schedules)
- [Basic Schedule Operations](#basic-schedule-operations)
- [Schedule Data Objects](#schedule-data-objects)
- [Schedule Phases](#schedule-phases)
- [Schedule Lifecycle](#schedule-lifecycle)
- [Testing Subscription Schedules](#testing-subscription-schedules)
- [Common Patterns](#common-patterns)

## Understanding Subscription Schedules

Subscription schedules are Stripe's solution for managing subscription changes over time. Unlike immediate subscription modifications, schedules allow you to:

- **Plan Future Changes** - Set up pricing changes months in advance
- **Create Complex Pricing Models** - Different rates for different time periods
- **Handle Promotional Periods** - Temporary discounts with automatic reversion
- **Manage Seasonal Pricing** - Different rates for peak/off-peak periods

```php
// Basic schedule flow
// 1. Create a schedule with phases
$schedule = Stripe::subscriptionSchedules()->create(Stripe::builder()->subscriptionSchedule()->build(
    customer: 'cus_123',
    startDate: now()->addDays(30),
    phases: [
        [
            'items' => [['price' => 'price_intro', 'quantity' => 1]],
            'duration' => ['interval' => 'month', 'interval_count' => 3]
        ],
        [
            'items' => [['price' => 'price_regular', 'quantity' => 1]]
        ]
    ]
));

// 2. Stripe automatically:
//    - Creates the subscription at the start date
//    - Transitions between phases as scheduled
//    - Handles billing and invoicing for each phase

// 3. You can modify or cancel the schedule before it completes
$schedule = Stripe::subscriptionSchedules()->update('sub_sched_123', $updatedSchedule);
```

## Basic Schedule Operations

The subscription schedule service (accessed via `Stripe::subscriptionSchedules()`) provides methods for schedule management.

### Accessing Schedules from Subscriptions

Schedules can be accessed directly from subscription objects using the fluent nested object pattern:

```php
use EncoreDigitalGroup\Stripe\Objects\Subscription\StripeSubscription;
use EncoreDigitalGroup\Stripe\Objects\Subscription\Schedules\StripePhaseItem;

// Get subscription and access its schedule
$subscription = StripeSubscription::make()->get('sub_123');

// Pattern 1: Fetch and modify schedule
$subscription->schedule()
    ->get()                                    // Fetch schedule from Stripe
    ->addPhase(                               // Add a new phase
        StripePhaseItem::make()
            ->withPrice('price_new')
            ->withQuantity(1)
    )
    ->save();                                  // Save schedule to Stripe

// Pattern 2: Save subscription and schedule together
$subscription = StripeSubscription::make()->get('sub_123');
$subscription->schedule()
    ->get()
    ->addPhase(
        StripePhaseItem::make()
            ->withPrice('price_upgraded')
            ->withQuantity(1)
    );
$subscription->save();                         // Saves both subscription AND schedule

// Pattern 3: Standalone schedule access
$schedule = StripeSubscriptionSchedule::make()
    ->get('sub_123')                          // Fetch by subscription ID
    ->addPhase(
        StripePhaseItem::make()
            ->withPrice('price_new')
            ->withQuantity(1)
    )
    ->save();
```

**Key Features:**
- **Fluent API** - Chain methods for clean, readable code
- **Immutability** - `get()` and `save()` return new instances with fresh data
- **Auto-caching** - Schedule is cached on the subscription to avoid redundant API calls
- **Automatic parent reference** - The schedule always knows its parent subscription

### Creating Subscription Schedules

```php
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Enums\SubscriptionScheduleProrationBehavior;
use Carbon\Carbon;

// Simple future subscription start
$schedule = Stripe::subscriptionSchedules()->create(Stripe::builder()->subscriptionSchedule()->build(
    customer: 'cus_customer123',
    startDate: Carbon::now()->addDays(7), // Start in 7 days
    phases: [
        [
            'items' => [['price' => 'price_monthly', 'quantity' => 1]]
        ]
    ]
));

// Multi-phase pricing schedule
$schedule = Stripe::subscriptionSchedules()->create(Stripe::builder()->subscriptionSchedule()->build(
    customer: 'cus_customer123',
    startDate: Carbon::now()->addDays(1),
    phases: [
        [
            // 3-month introductory pricing
            'items' => [['price' => 'price_intro_50_percent', 'quantity' => 1]],
            'duration' => ['interval' => 'month', 'interval_count' => 3],
            'prorationBehavior' => SubscriptionScheduleProrationBehavior::None
        ],
        [
            // Regular pricing thereafter
            'items' => [['price' => 'price_regular', 'quantity' => 1]]
        ]
    ],
    metadata: [
        'promotion' => 'new_customer_discount',
        'created_by' => 'admin_user_123'
    ]
));

echo $schedule->id; // "sub_sched_abc123..."
echo $schedule->status->value; // "not_started"
```

### Retrieving Subscription Schedules

```php
// Get by ID
$schedule = Stripe::subscriptionSchedules()->retrieve('sub_sched_abc123');

// Access all properties with full type safety
echo $schedule->customer;                    // "cus_123"
echo $schedule->status->value;               // "active"
echo $schedule->startDate->toDateTimeString(); // Start date
echo $schedule->endBehavior->value;          // "release"

// Check schedule phases
foreach ($schedule->phases as $phase) {
    echo "Phase duration: {$phase->duration['interval_count']} {$phase->duration['interval']}\n";
    foreach ($phase->items as $item) {
        echo "Price: {$item['price']}, Quantity: {$item['quantity']}\n";
    }
}
```

### Updating Subscription Schedules

```php
// Update schedule phases before it starts
$updatedSchedule = Stripe::subscriptionSchedules()->update('sub_sched_abc123',
    Stripe::builder()->subscriptionSchedule()->build(
        phases: [
            [
                'items' => [['price' => 'price_updated_intro', 'quantity' => 1]],
                'duration' => ['interval' => 'month', 'interval_count' => 2] // Shortened intro
            ],
            [
                'items' => [['price' => 'price_premium', 'quantity' => 1]] // Upgraded plan
            ]
        ]
    )
);

// Update metadata
$updatedSchedule = Stripe::subscriptionSchedules()->update('sub_sched_abc123',
    Stripe::builder()->subscriptionSchedule()->build(
        metadata: [
            'updated_at' => now()->toISOString(),
            'reason' => 'customer_requested_upgrade'
        ]
    )
);
```

### Listing Subscription Schedules

```php
// List all schedules
$schedules = Stripe::subscriptionSchedules()->all();

// Filter by customer
$customerSchedules = Stripe::subscriptionSchedules()->all(
    customer: 'cus_123',
    limit: 10
);

// Work with the collection
$activeSchedules = $schedules->filter(fn($schedule) =>
    $schedule->status === SubscriptionScheduleStatus::Active
);
```

## Schedule Data Objects

The `StripeSubscriptionSchedule` class provides a strongly-typed representation of schedule data.

### StripeSubscriptionSchedule Properties

```php
use EncoreDigitalGroup\Stripe\Objects\Subscription\Schedules\StripeSubscriptionSchedule;
use EncoreDigitalGroup\Stripe\Enums\{SubscriptionScheduleStatus, SubscriptionScheduleEndBehavior};
use Carbon\CarbonImmutable;

$schedule = Stripe::builder()->subscriptionSchedule()->build(
    id: 'sub_sched_123',                                    // string|null - Schedule ID
    customer: 'cus_123',                                    // string|null - Customer ID (required)
    subscription: 'sub_123',                                // string|null - Created subscription ID
    status: SubscriptionScheduleStatus::Active,             // SubscriptionScheduleStatus|null
    startDate: now()->addDays(7),                          // CarbonImmutable|null - When to start
    endBehavior: SubscriptionScheduleEndBehavior::Release,  // SubscriptionScheduleEndBehavior|null
    canceledAt: null,                                       // CarbonImmutable|null - When canceled
    completedAt: null,                                      // CarbonImmutable|null - When completed
    releasedAt: null,                                       // CarbonImmutable|null - When released
    phases: [                                               // array|null - Schedule phases
        [
            'items' => [['price' => 'price_123', 'quantity' => 1]],
            'duration' => ['interval' => 'month', 'interval_count' => 3]
        ]
    ],
    currentPhase: null,                                     // StripeSubscriptionSchedulePhase|null
    defaultSettings: null,                                  // array|null - Default phase settings
    metadata: ['key' => 'value']                           // array|null - Custom metadata
);
```

### Schedule Status Enum

```php
use EncoreDigitalGroup\Stripe\Enums\SubscriptionScheduleStatus;

// Available statuses
SubscriptionScheduleStatus::NotStarted     // Schedule created, not yet active
SubscriptionScheduleStatus::Active         // Schedule is running
SubscriptionScheduleStatus::Completed      // All phases completed
SubscriptionScheduleStatus::Released       // Released to regular subscription
SubscriptionScheduleStatus::Canceled       // Canceled before completion

// Usage
if ($schedule->status === SubscriptionScheduleStatus::Active) {
    echo "Schedule is currently running";
}
```

### End Behavior Enum

```php
use EncoreDigitalGroup\Stripe\Enums\SubscriptionScheduleEndBehavior;

// What happens when schedule completes
SubscriptionScheduleEndBehavior::Release   // Convert to regular subscription (default)
SubscriptionScheduleEndBehavior::Cancel    // Cancel the subscription

$schedule = Stripe::builder()->subscriptionSchedule()->build(
    customer: 'cus_123',
    endBehavior: SubscriptionScheduleEndBehavior::Release,
    phases: [...]
);
```

### Proration Behavior Enum

```php
use EncoreDigitalGroup\Stripe\Enums\SubscriptionScheduleProrationBehavior;

// How to handle proration during phase transitions
SubscriptionScheduleProrationBehavior::CreateProrations  // Calculate pro-rata charges
SubscriptionScheduleProrationBehavior::None              // No proration
SubscriptionScheduleProrationBehavior::AlwaysInvoice     // Always create invoice

// Usage in phase definition
$phases = [
    [
        'items' => [['price' => 'price_intro']],
        'proration_behavior' => SubscriptionScheduleProrationBehavior::None->value
    ]
];
```

## Schedule Phases

Phases define the different periods in a subscription schedule, each with their own pricing and duration.

### StripeSubscriptionSchedulePhase Properties

```php
use EncoreDigitalGroup\Stripe\Objects\Subscription\Schedules\StripeSubscriptionSchedulePhase;

$phase = Stripe::builder()->subscriptionSchedulePhase()->build(
    startDate: now(),                                     // CarbonImmutable|null - Phase start
    endDate: now()->addMonths(3),                        // CarbonImmutable|null - Phase end
    items: [                                             // array|null - Phase items
        ['price' => 'price_123', 'quantity' => 1]
    ],
    prorationBehavior: SubscriptionScheduleProrationBehavior::CreateProrations, // ProrationBehavior|null
    currency: 'usd',                                     // string|null - Currency
    defaultPaymentMethod: 'pm_card123',                  // string|null - Payment method
    description: 'Introductory pricing phase',          // string|null - Description
    discounts: null,                                     // array|null - Phase discounts
    invoiceSettings: null,                               // array|null - Invoice settings
    metadata: ['phase' => 'intro']                      // array|null - Phase metadata
);
```

### Creating Complex Phases

```php
// Seasonal pricing schedule
$schedule = Stripe::subscriptionSchedules()->create(Stripe::builder()->subscriptionSchedule()->build(
    customer: 'cus_retail_customer',
    startDate: Carbon::parse('2025-12-01'), // Start of holiday season
    phases: [
        [
            // Holiday season pricing (Dec-Jan)
            'items' => [['price' => 'price_holiday_premium', 'quantity' => 1]],
            'duration' => ['interval' => 'month', 'interval_count' => 2],
            'description' => 'Holiday season premium pricing',
            'metadata' => ['season' => 'holiday']
        ],
        [
            // Regular pricing (Feb-Nov)
            'items' => [['price' => 'price_regular', 'quantity' => 1]],
            'duration' => ['interval' => 'month', 'interval_count' => 10],
            'description' => 'Standard pricing',
            'metadata' => ['season' => 'regular']
        ]
    ],
    endBehavior: SubscriptionScheduleEndBehavior::Release
));

// Graduated pricing schedule
$schedule = Stripe::subscriptionSchedules()->create(Stripe::builder()->subscriptionSchedule()->build(
    customer: 'cus_startup',
    phases: [
        [
            // Startup discount phase
            'items' => [['price' => 'price_startup_discount', 'quantity' => 1]],
            'duration' => ['interval' => 'month', 'interval_count' => 6],
            'metadata' => ['tier' => 'startup']
        ],
        [
            // Growth phase pricing
            'items' => [['price' => 'price_growth', 'quantity' => 1]],
            'duration' => ['interval' => 'month', 'interval_count' => 6],
            'metadata' => ['tier' => 'growth']
        ],
        [
            // Enterprise pricing
            'items' => [['price' => 'price_enterprise', 'quantity' => 1]],
            'metadata' => ['tier' => 'enterprise']
        ]
    ]
));
```

## Schedule Lifecycle

Subscription schedules move through various states during their lifecycle.

### Canceling Schedules

```php
// Cancel schedule before it starts
$canceledSchedule = Stripe::subscriptionSchedules()->cancel('sub_sched_123');

echo $canceledSchedule->status->value; // "canceled"
echo $canceledSchedule->canceledAt->toDateTimeString(); // When canceled

// Cancel with invoice options
$canceledSchedule = Stripe::subscriptionSchedules()->cancel(
    'sub_sched_123',
    invoiceNow: true,   // Create final invoice
    prorate: true       // Prorate final charges
);
```

### Releasing Schedules

Releasing a schedule converts it to a regular subscription, ending the scheduled phases.

```php
// Release schedule to regular subscription
$releasedSchedule = Stripe::subscriptionSchedules()->release('sub_sched_123');

echo $releasedSchedule->status->value; // "released"
echo $releasedSchedule->subscription;  // ID of the regular subscription

// Release with options
$releasedSchedule = Stripe::subscriptionSchedules()->release(
    'sub_sched_123',
    preserveCancelDate: true // Keep any scheduled cancellation
);
```

### Status Transitions

```php
/**
 * Schedule lifecycle states:
 *
 * 1. not_started → active (schedule start date reached)
 * 2. active → active (transitioning between phases)
 * 3. active → completed (all phases finished, endBehavior: cancel)
 * 4. active → released (released to regular subscription)
 * 5. any → canceled (manually canceled)
 */

// Handle different statuses
match($schedule->status) {
    SubscriptionScheduleStatus::NotStarted => handlePendingSchedule($schedule),
    SubscriptionScheduleStatus::Active => handleActiveSchedule($schedule),
    SubscriptionScheduleStatus::Completed => handleCompletedSchedule($schedule),
    SubscriptionScheduleStatus::Released => handleReleasedSchedule($schedule),
    SubscriptionScheduleStatus::Canceled => handleCanceledSchedule($schedule),
    default => logUnexpectedStatus($schedule),
};
```

## Testing Subscription Schedules

The library provides testing utilities for subscription schedules.

### Basic Schedule Testing

```php
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Support\Testing\{StripeFixtures, StripeMethod};

test('can create a subscription schedule', function () {
    $fake = Stripe::fake([
        StripeMethod::SubscriptionSchedulesCreate->value => StripeFixtures::subscriptionSchedule([
            'id' => 'sub_sched_test123',
            'customer' => 'cus_123',
            'status' => 'not_started'
        ])
    ]);

    $schedule = Stripe::subscriptionSchedules()->create(Stripe::builder()->subscriptionSchedule()->build(
        customer: 'cus_123',
        startDate: now()->addDays(7),
        phases: [
            [
                'items' => [['price' => 'price_monthly', 'quantity' => 1]]
            ]
        ]
    ));

    expect($schedule)
        ->toBeInstanceOf(StripeSubscriptionSchedule::class)
        ->and($schedule->id)->toBe('sub_sched_test123')
        ->and($schedule->status)->toBe(SubscriptionScheduleStatus::NotStarted)
        ->and($fake)->toHaveCalledStripeMethod(StripeMethod::SubscriptionSchedulesCreate);
});
```

### Testing Schedule Phases

```php
test('creates schedule with multiple phases', function () {
    $fake = Stripe::fake([
        'subscription_schedules.create' => StripeFixtures::subscriptionSchedule([
            'id' => 'sub_sched_phases',
            'phases' => [
                StripeFixtures::subscriptionSchedulePhase([
                    'items' => [['price' => 'price_intro']],
                    'duration' => ['interval' => 'month', 'interval_count' => 3]
                ]),
                StripeFixtures::subscriptionSchedulePhase([
                    'items' => [['price' => 'price_regular']]
                ])
            ]
        ])
    ]);

    $schedule = Stripe::subscriptionSchedules()->create(Stripe::builder()->subscriptionSchedule()->build(
        customer: 'cus_123',
        phases: [
            [
                'items' => [['price' => 'price_intro']],
                'duration' => ['interval' => 'month', 'interval_count' => 3]
            ],
            [
                'items' => [['price' => 'price_regular']]
            ]
        ]
    ));

    expect($schedule->phases)->toHaveCount(2);
});
```

### Testing Schedule Lifecycle

```php
test('can cancel subscription schedule', function () {
    $fake = Stripe::fake([
        'subscription_schedules.cancel' => StripeFixtures::subscriptionSchedule([
            'id' => 'sub_sched_123',
            'status' => 'canceled',
            'canceled_at' => time()
        ])
    ]);

    $schedule = Stripe::subscriptionSchedules()->cancel('sub_sched_123');

    expect($schedule->status)->toBe(SubscriptionScheduleStatus::Canceled)
        ->and($schedule->canceledAt)->not->toBeNull()
        ->and($fake)->toHaveCalledStripeMethod('subscription_schedules.cancel');
});

test('can release subscription schedule', function () {
    $fake = Stripe::fake([
        'subscription_schedules.release' => StripeFixtures::subscriptionSchedule([
            'id' => 'sub_sched_123',
            'status' => 'released',
            'subscription' => 'sub_regular_123',
            'released_at' => time()
        ])
    ]);

    $schedule = Stripe::subscriptionSchedules()->release('sub_sched_123');

    expect($schedule->status)->toBe(SubscriptionScheduleStatus::Released)
        ->and($schedule->subscription)->toBe('sub_regular_123')
        ->and($fake)->toHaveCalledStripeMethod('subscription_schedules.release');
});
```

## Common Patterns

Here are real-world patterns for managing subscription schedules.

### Promotional Pricing Service

```php
class PromotionalPricingService
{
    public function __construct(
        private StripeSubscriptionScheduleService $scheduleService
    ) {}

    public function createNewCustomerDiscount(User $user, string $regularPriceId): StripeSubscriptionSchedule
    {
        return $this->scheduleService->create(Stripe::builder()->subscriptionSchedule()->build(
            customer: $user->stripe_customer_id,
            startDate: now()->addDay(), // Start tomorrow
            phases: [
                [
                    // 50% off for first 3 months
                    'items' => [['price' => 'price_50_percent_off', 'quantity' => 1]],
                    'duration' => ['interval' => 'month', 'interval_count' => 3],
                    'metadata' => ['promotion' => 'new_customer_50_off']
                ],
                [
                    // Regular pricing thereafter
                    'items' => [['price' => $regularPriceId, 'quantity' => 1]],
                    'metadata' => ['promotion' => 'none']
                ]
            ],
            metadata: [
                'user_id' => $user->id,
                'promotion_type' => 'new_customer',
                'created_at' => now()->toISOString()
            ]
        ));
    }

    public function createSeasonalPromotion(string $customerId, Carbon $startDate, Carbon $endDate): StripeSubscriptionSchedule
    {
        return $this->scheduleService->create(Stripe::builder()->subscriptionSchedule()->build(
            customer: $customerId,
            startDate: $startDate,
            phases: [
                [
                    // Promotional pricing during season
                    'items' => [['price' => 'price_seasonal_discount', 'quantity' => 1]],
                    'end_date' => $endDate->timestamp,
                    'metadata' => ['promotion' => 'seasonal']
                ],
                [
                    // Back to regular pricing
                    'items' => [['price' => 'price_regular', 'quantity' => 1]],
                    'metadata' => ['promotion' => 'none']
                ]
            ],
            endBehavior: SubscriptionScheduleEndBehavior::Release
        ));
    }

    public function extendPromotion(string $scheduleId, int $additionalMonths): StripeSubscriptionSchedule
    {
        $schedule = $this->scheduleService->retrieve($scheduleId);

        // Extend the first phase
        $phases = $schedule->phases;
        $phases[0]['duration']['interval_count'] += $additionalMonths;

        return $this->scheduleService->update($scheduleId, Stripe::builder()->subscriptionSchedule()->build(
            phases: $phases,
            metadata: array_merge($schedule->metadata ?? [], [
                'extended_at' => now()->toISOString(),
                'additional_months' => $additionalMonths
            ])
        ));
    }
}
```

### Contract-Based Pricing

```php
class ContractPricingService
{
    public function createAnnualContract(User $user, string $monthlyPriceId, string $annualPriceId): StripeSubscriptionSchedule
    {
        return Stripe::subscriptionSchedules()->create(Stripe::builder()->subscriptionSchedule()->build(
            customer: $user->stripe_customer_id,
            phases: [
                [
                    // 12-month contract at discounted annual rate
                    'items' => [['price' => $annualPriceId, 'quantity' => 1]],
                    'duration' => ['interval' => 'year', 'interval_count' => 1],
                    'metadata' => ['contract_type' => 'annual']
                ],
                [
                    // Revert to monthly after contract
                    'items' => [['price' => $monthlyPriceId, 'quantity' => 1]],
                    'metadata' => ['contract_type' => 'monthly']
                ]
            ],
            metadata: [
                'user_id' => $user->id,
                'contract_start' => now()->toISOString(),
                'contract_type' => 'annual_to_monthly'
            ]
        ));
    }

    public function createSteppedPricing(User $user): StripeSubscriptionSchedule
    {
        return Stripe::subscriptionSchedules()->create(Stripe::builder()->subscriptionSchedule()->build(
            customer: $user->stripe_customer_id,
            phases: [
                [
                    // Months 1-6: Startup pricing
                    'items' => [['price' => 'price_startup', 'quantity' => 1]],
                    'duration' => ['interval' => 'month', 'interval_count' => 6],
                    'metadata' => ['tier' => 'startup']
                ],
                [
                    // Months 7-12: Growth pricing
                    'items' => [['price' => 'price_growth', 'quantity' => 1]],
                    'duration' => ['interval' => 'month', 'interval_count' => 6],
                    'metadata' => ['tier' => 'growth']
                ],
                [
                    // Month 13+: Enterprise pricing
                    'items' => [['price' => 'price_enterprise', 'quantity' => 1]],
                    'metadata' => ['tier' => 'enterprise']
                ]
            ]
        ));
    }
}
```

### Schedule Analytics

```php
class ScheduleAnalyticsService
{
    public function getScheduleStats(): array
    {
        $schedules = Stripe::subscriptionSchedules()->all(['limit' => 100]);

        return [
            'total' => $schedules->count(),
            'not_started' => $schedules->where('status', SubscriptionScheduleStatus::NotStarted)->count(),
            'active' => $schedules->where('status', SubscriptionScheduleStatus::Active)->count(),
            'completed' => $schedules->where('status', SubscriptionScheduleStatus::Completed)->count(),
            'released' => $schedules->where('status', SubscriptionScheduleStatus::Released)->count(),
            'canceled' => $schedules->where('status', SubscriptionScheduleStatus::Canceled)->count(),
        ];
    }

    public function findUpcomingTransitions(int $days = 30): Collection
    {
        $cutoffDate = now()->addDays($days);

        return Stripe::subscriptionSchedules()->all()
            ->filter(function ($schedule) use ($cutoffDate) {
                if ($schedule->status !== SubscriptionScheduleStatus::Active) {
                    return false;
                }

                // Check if any phase ends within the timeframe
                foreach ($schedule->phases as $phase) {
                    if (isset($phase->endDate) && $phase->endDate <= $cutoffDate) {
                        return true;
                    }
                }

                return false;
            });
    }

    public function getPromotionalSchedules(): Collection
    {
        return Stripe::subscriptionSchedules()->all()
            ->filter(function ($schedule) {
                $metadata = $schedule->metadata ?? [];
                return isset($metadata['promotion']) && $metadata['promotion'] !== 'none';
            });
    }
}
```

## Next Steps

Now that you understand subscription schedules, you can explore related topics:

- **[Subscriptions](07-subscriptions.md)** - Core subscription management
- **[Customers](02-customers.md)** - Managing customer data
- **[Prices](04-prices.md)** - Complex pricing models
- **[Testing](05-testing.md)** - Comprehensive testing strategies

Or explore other advanced features:

- **[Financial Connections](08-financial-connections.md)** - Bank account connections
- **[Webhooks](09-webhooks.md)** - Handling Stripe events
- **[Builders Reference](10-builders-reference.md)** - Complete builder documentation