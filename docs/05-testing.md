# Testing

Testing Stripe integrations traditionally requires mocking complex API responses or setting up test accounts. This library provides a comprehensive testing infrastructure
that makes testing Stripe integrations as simple as testing any other part of your Laravel application. This chapter covers everything you need to know about testing with
the Laravel Stripe library.

## Table of Contents

- [Testing Philosophy](#testing-philosophy)
- [Quick Start](#quick-start)
- [The Stripe::fake() Method](#the-stripefake-method)
- [StripeFixtures](#stripefixtures)
- [Custom Test Expectations](#custom-test-expectations)
- [Advanced Testing Patterns](#advanced-testing-patterns)
- [Testing Different Scenarios](#testing-different-scenarios)
- [Best Practices](#best-practices)
- [Real-World Examples](#real-world-examples)

## Testing Philosophy

The Laravel Stripe library follows Laravel's testing philosophy: **make testing easy and intuitive**. The testing infrastructure is inspired by Laravel's `Http::fake()`
method and provides similar capabilities for Stripe API calls.

### Key Principles

1. **No Network Calls**: Tests never hit the real Stripe API
2. **Realistic Data**: Fixtures mirror real Stripe API responses
3. **Type Safety**: Full IDE support and type checking in tests
4. **Assertion-Rich**: Custom expectations for common testing scenarios
5. **Easy Setup**: Minimal boilerplate to get started

### What Gets Tested

```php
// Your service code
$customer = Stripe::customers()->create(Stripe::customer(
    email: 'test@example.com',
    name: 'Test Customer'
));

// What the test verifies:
// 1. The correct Stripe API method was called
// 2. The correct parameters were sent
// 3. The response was properly converted to our DTOs
// 4. The business logic behaves correctly
```

## Quick Start

Here's a complete test example to understand the basics:

```php
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Support\Testing\{StripeFixtures, StripeMethod};

test('can create a customer', function () {
    // 1. Set up fake responses
    $fake = Stripe::fake([
        StripeMethod::CustomersCreate->value => StripeFixtures::customer([
            'id' => 'cus_test123',
            'email' => 'test@example.com',
            'name' => 'Test Customer'
        ])
    ]);

    // 2. Execute the code under test
    $customer = Stripe::customers()->create(Stripe::customer(
        email: 'test@example.com',
        name: 'Test Customer'
    ));

    // 3. Assert the results
    expect($customer)
        ->toBeInstanceOf(StripeCustomer::class)
        ->and($customer->id)->toBe('cus_test123')
        ->and($customer->email)->toBe('test@example.com')
        ->and($fake)->toHaveCalledStripeMethod(StripeMethod::CustomersCreate);
});
```

## The Stripe::fake() Method

The `Stripe::fake()` method is the heart of the testing system. It intercepts all Stripe API calls and returns predefined responses.

### Basic Usage

```php
// Fake a single method
Stripe::fake([
    'customers.create' => StripeFixtures::customer(['id' => 'cus_123'])
]);

// Fake multiple methods
Stripe::fake([
    'customers.create' => StripeFixtures::customer(),
    'customers.retrieve' => StripeFixtures::customer(['id' => 'cus_existing']),
    'products.create' => StripeFixtures::product()
]);
```

### Method Name Formats

You can specify Stripe methods in multiple ways:

```php
// String format (service.method)
Stripe::fake(['customers.create' => $response]);

// Enum format (type-safe)
Stripe::fake([StripeMethod::CustomersCreate->value => $response]);

// Mixed format
Stripe::fake([
    StripeMethod::CustomersCreate->value => $customerResponse,
    'products.create' => $productResponse
]);
```

### Wildcard Patterns

Use wildcards to match multiple methods:

```php
// Match any customer method
Stripe::fake(['customers.*' => StripeFixtures::customer()]);

// Match any create method
Stripe::fake(['*.create' => function($params) {
    // Dynamic response based on the method called
    return StripeFixtures::customer(['email' => $params['email']]);
}]);
```

### Callable Responses

Use functions for dynamic responses:

```php
Stripe::fake([
    'customers.create' => function (array $params) {
        return StripeFixtures::customer([
            'id' => 'cus_' . uniqid(),
            'email' => $params['email'] ?? 'default@example.com',
            'name' => $params['name'] ?? 'Default Name'
        ]);
    }
]);
```

### How It Works

```php
$fake = Stripe::fake([...]); // Returns FakeStripeClient instance

// 1. Binds to Laravel container as singleton
app()->bind(StripeClient::class, fn() => $fake);

// 2. Services automatically use the fake
$service = Stripe::customers(); // Gets fake client

// 3. API calls are intercepted and faked
$customer = $service->create($customerData); // No network call

// 4. You can inspect what was called
$fake->wasCalled('customers.create'); // true
$fake->getCall('customers.create'); // ['email' => '...', 'name' => '...']
```

## StripeFixtures

`StripeFixtures` provides realistic test data that mirrors actual Stripe API responses. This ensures your tests are testing against data structures that match production.

### Available Fixtures

```php
// Customer fixtures
StripeFixtures::customer();
StripeFixtures::customer(['email' => 'custom@example.com']);
StripeFixtures::customerList([...customers]);

// Product fixtures
StripeFixtures::product();
StripeFixtures::product(['name' => 'Custom Product']);
StripeFixtures::productList([...products]);

// Price fixtures
StripeFixtures::price();
StripeFixtures::price(['unit_amount' => 2999]);
StripeFixtures::priceList([...prices]);

// Subscription fixtures
StripeFixtures::subscription();
StripeFixtures::subscriptionList([...subscriptions]);

// Utility fixtures
StripeFixtures::deleted('cus_123', 'customer');
StripeFixtures::error('card_error', 'Your card was declined.');
StripeFixtures::bankAccount();
StripeFixtures::financialConnectionsAccount();
```

### Fixture Structure

Each fixture returns a complete, valid Stripe API response:

```php
$customer = StripeFixtures::customer();
// Returns:
[
    'id' => 'cus_randomid',
    'object' => 'customer',
    'address' => null,
    'balance' => 0,
    'created' => 1640995200,
    'currency' => 'usd',
    'default_source' => null,
    'delinquent' => false,
    'description' => 'Test Customer',
    'email' => 'test@example.com',
    'name' => 'Test Customer',
    // ... all other Stripe customer fields
]
```

### Customizing Fixtures

Override any field using the array parameter:

```php
$customer = StripeFixtures::customer([
    'id' => 'cus_specific_id',
    'email' => 'specific@example.com',
    'metadata' => ['tier' => 'premium'],
    'address' => [
        'line1' => '123 Test Street',
        'city' => 'Test City',
        'postal_code' => '12345',
        'country' => 'US'
    ]
]);
```

### Complex Fixtures

Create complex nested data:

```php
// Price with tiered billing
$tieredPrice = StripeFixtures::price([
    'billing_scheme' => 'tiered',
    'tiers_mode' => 'graduated',
    'tiers' => [
        ['up_to' => 1000, 'unit_amount' => 100],
        ['up_to' => 'inf', 'unit_amount' => 80]
    ]
]);

// Subscription with multiple items
$subscription = StripeFixtures::subscription([
    'items' => [
        'data' => [
            ['price' => StripeFixtures::price(['id' => 'price_1'])],
            ['price' => StripeFixtures::price(['id' => 'price_2'])]
        ]
    ]
]);
```

## Custom Test Expectations

The library extends Pest's expectations with Stripe-specific assertions.

### Available Expectations

```php
// Assert a method was called
expect($fake)->toHaveCalledStripeMethod('customers.create');
expect($fake)->toHaveCalledStripeMethod(StripeMethod::CustomersCreate);

// Assert a method was NOT called
expect($fake)->toNotHaveCalledStripeMethod('customers.delete');

// Assert call count
expect($fake)->toHaveCalledStripeMethodTimes('customers.create', 3);

// Assert method was called with specific parameters
expect($fake)->toHaveCalledStripeMethod('customers.create', [
    'email' => 'test@example.com',
    'name' => 'Test Customer'
]);
```

### Chaining Expectations

Chain multiple assertions for comprehensive testing:

```php
expect($fake)
    ->toHaveCalledStripeMethod('customers.create')
    ->toHaveCalledStripeMethod('products.create')
    ->toNotHaveCalledStripeMethod('customers.delete')
    ->toHaveCalledStripeMethodTimes('prices.create', 2);
```

### Parameter Inspection

```php
test('sends correct parameters', function () {
    $fake = Stripe::fake(['customers.create' => StripeFixtures::customer()]);

    Stripe::customers()->create(Stripe::customer(
        email: 'test@example.com',
        name: 'Test User'
    ));

    $params = $fake->getCall('customers.create');
    expect($params)
        ->toHaveKey('email', 'test@example.com')
        ->toHaveKey('name', 'Test User')
        ->not->toHaveKey('id'); // ID should be excluded on create
});
```

## Advanced Testing Patterns

### Testing Error Conditions

```php
test('handles API errors gracefully', function () {
    Stripe::fake([
        'customers.create' => function () {
            throw new \Stripe\Exception\CardException(
                'Your card was declined.',
                'param',
                'card_declined'
            );
        }
    ]);

    expect(fn() => Stripe::customers()->create(Stripe::customer(email: 'test@example.com')))
        ->toThrow(\Stripe\Exception\CardException::class, 'Your card was declined.');
});
```

### Testing with Multiple Calls

```php
test('handles multiple API calls', function () {
    $fake = Stripe::fake([
        'products.create' => StripeFixtures::product(['id' => 'prod_123']),
        'prices.create' => function($params) {
            return StripeFixtures::price([
                'id' => 'price_' . uniqid(),
                'product' => $params['product']
            ]);
        }
    ]);

    // Create product
    $product = Stripe::products()->create(Stripe::product(name: 'Test Product'));

    // Create prices for the product
    $monthlyPrice = Stripe::prices()->create(Stripe::price(
        product: $product->id,
        unitAmount: 999,
        currency: 'usd',
        type: PriceType::Recurring,
        recurring: Stripe::recurring(interval: RecurringInterval::Month)
    ));

    expect($fake)
        ->toHaveCalledStripeMethod('products.create')
        ->toHaveCalledStripeMethod('prices.create')
        ->toHaveCalledStripeMethodTimes('prices.create', 1);
});
```

### Testing Data Transformation

```php
test('converts between DTOs and API format correctly', function () {
    $fake = Stripe::fake([
        'customers.create' => StripeFixtures::customer([
            'address' => [
                'line1' => '123 Test St',
                'postal_code' => '12345'
            ]
        ])
    ]);

    $customer = Stripe::customers()->create(Stripe::customer(
        email: 'test@example.com',
        address: Stripe::address(
            line1: '123 Test St',
            postalCode: '12345'
        )
    ));

    // Verify conversion from camelCase to snake_case
    $params = $fake->getCall('customers.create');
    expect($params['address'])
        ->toHaveKey('line1', '123 Test St')
        ->toHaveKey('postal_code', '12345'); // Note: snake_case

    // Verify conversion back to camelCase
    expect($customer->address->postalCode)->toBe('12345'); // Note: camelCase
});
```

### Testing Service Dependencies

```php
test('services use injected Stripe client', function () {
    $fake = Stripe::fake(['customers.create' => StripeFixtures::customer()]);

    // Test with dependency injection
    $customer = Stripe::customers()->create(Stripe::customer(email: 'test@example.com'));

    expect($fake)->toHaveCalledStripeMethod('customers.create');
});
```

## Testing Different Scenarios

### Testing CRUD Operations

```php
describe('Customer CRUD operations', function () {
    test('can create customer', function () {
        $fake = Stripe::fake([
            'customers.create' => StripeFixtures::customer(['id' => 'cus_new'])
        ]);

        $customer = Stripe::customers()->create(Stripe::customer(
            email: 'create@example.com'
        ));

        expect($customer->id)->toBe('cus_new')
            ->and($fake)->toHaveCalledStripeMethod('customers.create');
    });

    test('can retrieve customer', function () {
        $fake = Stripe::fake([
            'customers.retrieve' => StripeFixtures::customer(['id' => 'cus_existing'])
        ]);

        $customer = Stripe::customers()->get('cus_existing');

        expect($customer->id)->toBe('cus_existing')
            ->and($fake)->toHaveCalledStripeMethod('customers.retrieve');
    });

    test('can update customer', function () {
        $fake = Stripe::fake([
            'customers.update' => StripeFixtures::customer([
                'id' => 'cus_123',
                'name' => 'Updated Name'
            ])
        ]);

        $customer = Stripe::customers()->update('cus_123', Stripe::customer(
            name: 'Updated Name'
        ));

        expect($customer->name)->toBe('Updated Name')
            ->and($fake)->toHaveCalledStripeMethod('customers.update');
    });

    test('can delete customer', function () {
        $fake = Stripe::fake([
            'customers.delete' => StripeFixtures::deleted('cus_123', 'customer')
        ]);

        $deleted = Stripe::customers()->delete('cus_123');

        expect($deleted)->toBeTrue()
            ->and($fake)->toHaveCalledStripeMethod('customers.delete');
    });
});
```

### Testing Complex Price Configurations

```php
describe('Complex pricing scenarios', function () {
    test('creates tiered pricing correctly', function () {
        $fake = Stripe::fake([
            'prices.create' => StripeFixtures::price([
                'billing_scheme' => 'tiered',
                'tiers_mode' => 'graduated'
            ])
        ]);

        $price = Stripe::prices()->create(Stripe::price(
            product: 'prod_123',
            currency: 'usd',
            type: PriceType::Recurring,
            billingScheme: BillingScheme::Tiered,
            tiersMode: TiersMode::Graduated,
            recurring: Stripe::recurring(interval: RecurringInterval::Month),
            tiers: [
                ['up_to' => 1000, 'unit_amount' => 100],
                ['up_to' => 'inf', 'unit_amount' => 80]
            ]
        ));

        $params = $fake->getCall('prices.create');
        expect($params)
            ->toHaveKey('billing_scheme', 'tiered')
            ->toHaveKey('tiers_mode', 'graduated')
            ->toHaveKey('tiers');
    });

    test('creates usage-based pricing correctly', function () {
        $fake = Stripe::fake([
            'prices.create' => StripeFixtures::price([
                'type' => 'recurring',
                'recurring' => [
                    'usage_type' => 'metered',
                    'aggregate_usage' => 'sum'
                ]
            ])
        ]);

        $price = Stripe::prices()->create(Stripe::price(
            product: 'prod_api',
            unitAmount: 1,
            currency: 'usd',
            type: PriceType::Recurring,
            recurring: Stripe::recurring(
                interval: RecurringInterval::Month,
                usageType: RecurringUsageType::Metered,
                aggregateUsage: RecurringAggregateUsage::Sum
            )
        ));

        $params = $fake->getCall('prices.create');
        expect($params['recurring'])
            ->toHaveKey('usage_type', 'metered')
            ->toHaveKey('aggregate_usage', 'sum');
    });
});
```

### Testing Business Logic

```php
test('customer registration flow', function () {
    $fake = Stripe::fake([
        'customers.create' => StripeFixtures::customer([
            'id' => 'cus_new_user',
            'email' => 'newuser@example.com'
        ])
    ]);

    // Your business logic
    $user = User::factory()->create(['email' => 'newuser@example.com']);
    $registrationService = new CustomerRegistrationService();

    $stripeCustomer = $registrationService->registerCustomer($user);

    // Assert Stripe was called
    expect($fake)->toHaveCalledStripeMethod('customers.create');

    // Assert business logic worked
    $user->refresh();
    expect($user->stripe_customer_id)->toBe('cus_new_user');
    expect($stripeCustomer->email)->toBe('newuser@example.com');
});
```

## Best Practices

### 1. Use Realistic Data

```php
// ✅ Good: Use fixtures for realistic data
$fake = Stripe::fake([
    'customers.create' => StripeFixtures::customer(['email' => 'test@example.com'])
]);

// ❌ Avoid: Minimal or unrealistic data
$fake = Stripe::fake([
    'customers.create' => ['id' => 'cus_123']
]);
```

### 2. Test Business Logic, Not Implementation

```php
// ✅ Good: Test the outcome
test('user gets premium features after subscribing', function () {
    Stripe::fake(['subscriptions.create' => StripeFixtures::subscription()]);

    $user = User::factory()->create();
    $subscriptionService = new SubscriptionService();

    $subscriptionService->subscribeToPremium($user);

    expect($user->fresh()->isPremium())->toBeTrue();
});

// ❌ Avoid: Testing implementation details
test('calls stripe subscription create with correct parameters', function () {
    // This is testing implementation, not business value
});
```

### 3. Use Descriptive Test Names

```php
// ✅ Good: Clear intent
test('creates customer with billing address when address provided');
test('throws exception when customer email is invalid');
test('archives price instead of deleting to preserve billing history');

// ❌ Avoid: Generic names
test('customer creation works');
test('price deletion');
```

### 4. Group Related Tests

```php
describe('Customer address management', function () {
    test('can create customer with billing address');
    test('can update customer billing address');
    test('can create customer with shipping address');
    test('handles invalid address data gracefully');
});

describe('Price lifecycle management', function () {
    test('can archive active price');
    test('can reactivate archived price');
    test('prevents deletion of prices with active subscriptions');
});
```

### 5. Test Edge Cases

```php
test('handles empty metadata correctly', function () {
    $fake = Stripe::fake([
        'customers.create' => StripeFixtures::customer(['metadata' => []])
    ]);

    $customer = Stripe::customers()->create(Stripe::customer(
        email: 'test@example.com',
        metadata: []
    ));

    expect($customer->metadata)->toBe([]);
});

test('handles null address gracefully', function () {
    $fake = Stripe::fake([
        'customers.create' => StripeFixtures::customer(['address' => null])
    ]);

    $customer = Stripe::customers()->create(Stripe::customer(
        email: 'test@example.com',
        address: null
    ));

    expect($customer->address)->toBeNull();
});
```

## Real-World Examples

### E-commerce Checkout Flow

```php
describe('E-commerce checkout flow', function () {
    test('complete purchase flow', function () {
        $fake = Stripe::fake([
            'customers.create' => StripeFixtures::customer(['id' => 'cus_buyer']),
            'prices.retrieve' => StripeFixtures::price(['id' => 'price_product']),
            'subscriptions.create' => StripeFixtures::subscription([
                'customer' => 'cus_buyer',
                'status' => 'active'
            ])
        ]);

        // Simulate checkout process
        $checkoutService = new CheckoutService();
        $user = User::factory()->create();

        $result = $checkoutService->processCheckout($user, [
            'price_id' => 'price_product',
            'quantity' => 1
        ]);

        expect($fake)
            ->toHaveCalledStripeMethod('customers.create')
            ->toHaveCalledStripeMethod('subscriptions.create');

        expect($result['status'])->toBe('success');
        expect($user->fresh()->hasActiveSubscription())->toBeTrue();
    });
});
```

### SaaS Subscription Management

```php
describe('SaaS subscription management', function () {
    test('user can upgrade subscription plan', function () {
        $fake = Stripe::fake([
            'subscriptions.retrieve' => StripeFixtures::subscription([
                'id' => 'sub_current',
                'status' => 'active'
            ]),
            'subscriptions.update' => StripeFixtures::subscription([
                'id' => 'sub_current',
                'status' => 'active'
                // Updated with new price
            ])
        ]);

        $user = User::factory()->create(['stripe_subscription_id' => 'sub_current']);
        $subscriptionService = new SubscriptionService();

        $subscriptionService->changePlan($user, 'price_premium');

        expect($fake)
            ->toHaveCalledStripeMethod('subscriptions.retrieve')
            ->toHaveCalledStripeMethod('subscriptions.update');
    });

    test('handles failed subscription cancellation', function () {
        $fake = Stripe::fake([
            'subscriptions.update' => function() {
                throw new \Stripe\Exception\ApiErrorException(
                    'Subscription cannot be canceled'
                );
            }
        ]);

        $user = User::factory()->create(['stripe_subscription_id' => 'sub_123']);
        $subscriptionService = new SubscriptionService();

        expect(fn() => $subscriptionService->cancelSubscription($user))
            ->toThrow(\Stripe\Exception\ApiErrorException::class);

        // Verify user subscription status wasn't changed
        expect($user->fresh()->hasActiveSubscription())->toBeTrue();
    });
});
```

### API Usage Billing

```php
describe('API usage billing', function () {
    test('records usage for metered billing', function () {
        $fake = Stripe::fake([
            'subscription_items.all' => StripeFixtures::subscriptionList([
                StripeFixtures::subscription(['id' => 'si_usage'])
            ]),
            'usage_records.create' => ['id' => 'mbur_usage_record']
        ]);

        $usageService = new ApiUsageService();
        $user = User::factory()->create(['stripe_subscription_id' => 'sub_123']);

        $usageService->recordApiUsage($user, 1000); // 1000 API calls

        expect($fake)->toHaveCalledStripeMethod('usage_records.create');

        $params = $fake->getCall('usage_records.create');
        expect($params)
            ->toHaveKey('quantity', 1000)
            ->toHaveKey('timestamp');
    });
});
```

### Webhook Processing

```php
describe('Webhook processing', function () {
    test('processes successful payment webhook', function () {
        $webhookPayload = [
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_test123',
                    'customer' => 'cus_test123',
                    'status' => 'succeeded'
                ]
            ]
        ];

        $webhookHandler = new StripeWebhookHandler();
        $result = $webhookHandler->handle($webhookPayload);

        expect($result)->toBe('success');

        // Verify business logic was executed
        $user = User::where('stripe_customer_id', 'cus_test123')->first();
        expect($user->last_payment_at)->not->toBeNull();
    });
});
```

## Next Steps

Now that you understand testing, you can explore the architectural foundations:

- **[Architecture](06-architecture.md)** - Deep dive into library design, patterns, and extensibility

Or revisit the functional documentation:

- **[Getting Started](01-getting-started.md)** - Basic concepts and setup
- **[Customers](02-customers.md)** - Customer management
- **[Products](03-products.md)** - Product lifecycle
- **[Prices](04-prices.md)** - Complex pricing models