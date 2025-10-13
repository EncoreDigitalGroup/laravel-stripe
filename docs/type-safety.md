# Type Safety Guide

One of the core benefits of this library is strong type safety. This guide explains how types protect you from errors and improve your development experience.

## The Problem with Magic String APIs

The official Stripe PHP SDK uses arrays and magic strings:

```php
$stripe->prices->create([
    'product' => 'prod_123',
    'unit_amount' => 1000,
    'currency' => 'usd',
    'recurring' => [
        'interval' => 'month', // Typo? 'monthly'? 'Month'?
        'interval_count' => 1
    ]
]);
```

Problems with this approach:

- Typos cause runtime errors
- No IDE autocomplete
- No compile-time validation
- Documentation required for every operation
- Refactoring is dangerous

## The Type-Safe Approach

This library wraps everything in strongly-typed objects:

```php
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Enums\RecurringInterval;

$price = Stripe::prices()->create(
    Stripe::price(
        product: 'prod_123',
        unitAmount: 1000,
        currency: 'usd',
        recurring: [
            'interval' => RecurringInterval::Month, // IDE autocompletes
            'interval_count' => 1
        ]
    )
);
```

Benefits:

- Typos become compiler errors
- Full IDE autocomplete support
- Type hints guide usage
- Refactoring is safe
- Self-documenting code

## Data Transfer Objects (DTOs)

Every Stripe object is represented as a DTO with named properties.

### Properties Are Always Nullable

All DTO properties are nullable for three reasons:

1. **Flexibility** - Stripe's API makes most fields optional
2. **Partial Updates** - You rarely need to specify every field
3. **Read/Write Differences** - Some fields are read-only

```php
// Create with only required fields
$customer = Stripe::customer(
    email: 'customer@example.com'
);

// All other properties are null
var_dump($customer->name); // null
var_dump($customer->phone); // null
```

### Named Parameters

All factory methods use named parameters for clarity:

```php
// Clear and explicit
$product = Stripe::product(
    name: 'Premium Plan',
    description: 'Full access',
    active: true
);

// Order doesn't matter
$product = Stripe::product(
    active: true,
    name: 'Premium Plan',
    description: 'Full access'
);
```

### Property Naming Convention

Properties use camelCase, but automatically convert to snake_case for the Stripe API:

```php
// Your code (camelCase)
$customer = Stripe::customer(
    email: 'test@example.com',
    defaultSource: 'card_...'
);

// Stripe API receives (snake_case)
// {
//   "email": "test@example.com",
//   "default_source": "card_..."
// }
```

## Enums: Type-Safe Constants

Enums replace magic strings with type-safe constants.

### Common Enums

**Subscription Status**

```php
use EncoreDigitalGroup\Stripe\Enums\SubscriptionStatus;

SubscriptionStatus::Active
SubscriptionStatus::Canceled
SubscriptionStatus::Incomplete
SubscriptionStatus::IncompleteExpired
SubscriptionStatus::PastDue
SubscriptionStatus::Paused
SubscriptionStatus::Trialing
SubscriptionStatus::Unpaid
```

**Recurring Interval**

```php
use EncoreDigitalGroup\Stripe\Enums\RecurringInterval;

RecurringInterval::Day
RecurringInterval::Week
RecurringInterval::Month
RecurringInterval::Year
```

**Price Type**

```php
use EncoreDigitalGroup\Stripe\Enums\PriceType;

PriceType::OneTime
PriceType::Recurring
```

**Billing Scheme**

```php
use EncoreDigitalGroup\Stripe\Enums\BillingScheme;

BillingScheme::PerUnit
BillingScheme::Tiered
```

### Using Enums

Enums are string-backed, meaning their `value` property contains the actual Stripe API value:

```php
$interval = RecurringInterval::Month;

echo $interval->value; // 'month'
echo $interval->name;  // 'Month'
```

When creating objects, use the enum directly:

```php
$price = Stripe::price(
    product: 'prod_123',
    unitAmount: 1000,
    currency: 'usd',
    type: PriceType::Recurring,
    recurring: [
        'interval' => RecurringInterval::Month
    ]
);
```

### Enum Comparison

Compare enums using strict equality:

```php
if ($subscription->status === SubscriptionStatus::Active) {
    // Subscription is active
}

// NOT with strings
if ($subscription->status === 'active') { // Wrong! Type error
    // ...
}
```

### Converting from Strings

If you receive a string from user input or external source:

```php
$intervalString = 'month';
$interval = RecurringInterval::from($intervalString);

// Or safely with tryFrom (returns null if invalid)
$interval = RecurringInterval::tryFrom($intervalString);
if ($interval === null) {
    throw new \InvalidArgumentException('Invalid interval');
}
```

## Nested Objects

Some Stripe objects contain nested structures. The library handles these with arrays or additional DTOs.

### Simple Nested Structures (Arrays)

For simple structures like recurring settings:

```php
$price = Stripe::price(
    product: 'prod_123',
    unitAmount: 1000,
    currency: 'usd',
    recurring: [
        'interval' => RecurringInterval::Month,
        'interval_count' => 1,
        'trial_period_days' => 14
    ]
);
```

### Complex Nested Structures (DTOs)

For complex structures like addresses:

```php
$address = Stripe::address(
    line1: '123 Main St',
    line2: 'Apt 4',
    city: 'San Francisco',
    state: 'CA',
    postalCode: '94102',
    country: 'US'
);

$customer = Stripe::customer(
    email: 'test@example.com',
    address: $address
);
```

## Type Hints and IDE Support

The library is fully annotated for IDE support:

```php
// Your IDE knows the return type
$customer = Stripe::customers()->get('cus_123');

// Autocomplete shows available properties
$customer->email // IDE suggests: email, name, phone, etc.

// Autocomplete shows available methods
Stripe::customers()-> // IDE suggests: create, get, update, delete, list

// Enum autocomplete
RecurringInterval:: // IDE suggests: Day, Week, Month, Year
```

## Collection Types

List operations return typed collections:

```php
// Returns Collection<int, StripeCustomer>
$customers = Stripe::customers()->list();

// Each item is a StripeCustomer
foreach ($customers as $customer) {
    echo $customer->email; // Type-safe access
}

// Collection methods work
$activeCustomers = $customers->filter(
    fn($customer) => $customer->email !== null
);
```

## Null Safety Patterns

### Null Coalescing

Use null coalescing for defaults:

```php
$name = $customer->name ?? 'Guest';
$phone = $customer->phone ?? 'No phone';
```

### Null-Safe Operator

Use the null-safe operator for chained access:

```php
// Safe even if address is null
$city = $customer->address?->city ?? 'Unknown';
```

### Explicit Null Checks

Check for null before accessing:

```php
if ($customer->address !== null) {
    echo $customer->address->city;
}
```

## Metadata Handling

Metadata is always an array, never null:

```php
$customer = Stripe::customer(
    email: 'test@example.com',
    metadata: [
        'user_id' => '12345',
        'source' => 'website'
    ]
);

// Metadata is always an array
$metadata = $customer->metadata ?? [];
```

## Best Practices

### 1. Always Use Enums for Constants

```php
// Good
'interval' => RecurringInterval::Month

// Bad
'interval' => 'month'
```

### 2. Use Named Parameters

```php
// Good - clear intent
Stripe::customer(
    email: 'test@example.com',
    name: 'John Doe'
)

// Bad - unclear what each value represents
Stripe::customer('test@example.com', 'John Doe')
```

### 3. Leverage IDE Autocomplete

Let your IDE guide you. Type `Stripe::` and see what's available. Type `RecurringInterval::` to see valid intervals.

### 4. Use Type Hints

```php
use EncoreDigitalGroup\Stripe\Objects\Customer\StripeCustomer;

function processCustomer(StripeCustomer $customer): void {
    // Type safety throughout your application
}
```

### 5. Handle Nulls Gracefully

```php
// Provide sensible defaults
$name = $customer->name ?? 'Unknown Customer';

// Or fail explicitly
if ($customer->email === null) {
    throw new \RuntimeException('Customer email is required');
}
```

## Summary

Type safety provides:

- **Compile-time validation** - Catch errors before runtime
- **IDE support** - Autocomplete and type hints
- **Self-documenting** - Types explain usage
- **Refactoring safety** - Change with confidence
- **Better debugging** - Clear error messages

By embracing type safety, you write more maintainable code with fewer bugs.
