# Testing Guide

Testing Stripe integrations without making real API calls is crucial. This library provides a comprehensive fake client system modeled after Laravel's `Http::fake()`.

## The Testing Philosophy

**Never hit real Stripe APIs in tests**. Real API calls are:

- Slow (network latency)
- Unreliable (network issues)
- Expensive (API rate limits)
- Stateful (test data persists)
- Unpredictable (concurrent test conflicts)

The fake client solves all these problems.

## Basic Usage

### Setup Fake Client

```php
use EncoreDigitalGroup\Common\Stripe\Stripe;
use Tests\Support\StripeFixtures;
use Tests\Support\StripeMethod;

test('can create a customer', function () {
    // Setup fake responses
    Stripe::fake([
        StripeMethod::CustomersCreate->value => StripeFixtures::customer([
            'id' => 'cus_test123',
            'email' => 'test@example.com',
        ]),
    ]);

    // Use service normally - automatically uses fake
    $customer = Stripe::customers()->create(
        Stripe::customer(email: 'test@example.com')
    );

    // Assert results
    expect($customer->id)->toBe('cus_test123');
    expect($customer->email)->toBe('test@example.com');
});
```

**How it works**:

1. `Stripe::fake()` binds a fake client to Laravel's container
2. Services automatically resolve the fake client
3. API calls return your predefined responses
4. No network requests are made

## Fake Response Mapping

Map Stripe methods to responses:

```php
Stripe::fake([
    'customers.create' => StripeFixtures::customer(['id' => 'cus_123']),
    'customers.retrieve' => StripeFixtures::customer(['id' => 'cus_123']),
    'products.create' => StripeFixtures::product(['id' => 'prod_123']),
]);
```

### Method Naming

Methods follow the pattern: `resource.operation`

- `customers.create`
- `customers.retrieve`
- `customers.update`
- `customers.delete`
- `customers.all` (list)
- `products.create`
- `prices.create`
- `subscriptions.create`

### Using StripeMethod Enum

For type safety, use the `StripeMethod` enum:

```php
use Tests\Support\StripeMethod;

Stripe::fake([
    StripeMethod::CustomersCreate->value => StripeFixtures::customer(),
    StripeMethod::ProductsCreate->value => StripeFixtures::product(),
    StripeMethod::PricesCreate->value => StripeFixtures::price(),
]);
```

## Fixtures: Realistic Test Data

Always use fixtures instead of raw arrays. Fixtures provide complete, valid Stripe responses.

### Customer Fixtures

```php
use Tests\Support\StripeFixtures;

// Default customer
$customer = StripeFixtures::customer();

// Override specific fields
$customer = StripeFixtures::customer([
    'id' => 'cus_123',
    'email' => 'custom@example.com',
    'name' => 'Custom Name',
]);

// Customer list
$customers = StripeFixtures::customerList([
    StripeFixtures::customer(['id' => 'cus_1']),
    StripeFixtures::customer(['id' => 'cus_2']),
]);
```

### Product Fixtures

```php
// Default product
$product = StripeFixtures::product();

// With overrides
$product = StripeFixtures::product([
    'id' => 'prod_123',
    'name' => 'Custom Product',
    'active' => true,
]);

// Product list
$products = StripeFixtures::productList([
    StripeFixtures::product(['id' => 'prod_1']),
    StripeFixtures::product(['id' => 'prod_2']),
]);
```

### Price Fixtures

```php
// One-time price
$price = StripeFixtures::price([
    'id' => 'price_123',
    'unit_amount' => 2000,
    'currency' => 'usd',
]);

// Recurring price
$price = StripeFixtures::price([
    'id' => 'price_monthly',
    'unit_amount' => 999,
    'currency' => 'usd',
    'recurring' => [
        'interval' => 'month',
        'interval_count' => 1,
    ],
]);

// Price list
$prices = StripeFixtures::priceList([
    StripeFixtures::price(['id' => 'price_1']),
    StripeFixtures::price(['id' => 'price_2']),
]);
```

### Subscription Fixtures

```php
// Default subscription
$subscription = StripeFixtures::subscription();

// With overrides
$subscription = StripeFixtures::subscription([
    'id' => 'sub_123',
    'customer' => 'cus_123',
    'status' => 'active',
]);

// Subscription list
$subscriptions = StripeFixtures::subscriptionList([
    StripeFixtures::subscription(['id' => 'sub_1']),
    StripeFixtures::subscription(['id' => 'sub_2']),
]);
```

## Wildcard Responses

Use wildcards to match multiple methods:

```php
// Match all customer methods
Stripe::fake([
    'customers.*' => StripeFixtures::customer(['id' => 'cus_default']),
]);

// Now all customer operations return the same fixture
$created = Stripe::customers()->create(Stripe::customer(email: 'test@example.com'));
$retrieved = Stripe::customers()->get('cus_any');
$updated = Stripe::customers()->update('cus_any', Stripe::customer(name: 'New Name'));

// All return the same fixture
```

**Use case**: When you don't care about the specific response, just need something valid.

## Dynamic Responses with Callables

Return different responses based on input:

```php
Stripe::fake([
    'customers.create' => function (array $params) {
        return StripeFixtures::customer([
            'id' => 'cus_' . uniqid(),
            'email' => $params['email'] ?? 'default@example.com',
            'name' => $params['name'] ?? null,
        ]);
    },
]);

// Response reflects input
$customer1 = Stripe::customers()->create(
    Stripe::customer(email: 'alice@example.com')
);
// Returns customer with alice@example.com

$customer2 = Stripe::customers()->create(
    Stripe::customer(email: 'bob@example.com')
);
// Returns customer with bob@example.com
```

**Use case**: Testing behavior that depends on input values.

## Custom Pest Expectations

The library provides custom expectations for asserting fake behavior.

### Assert Method Was Called

```php
test('creates customer in Stripe', function () {
    $fake = Stripe::fake([
        'customers.create' => StripeFixtures::customer(),
    ]);

    Stripe::customers()->create(
        Stripe::customer(email: 'test@example.com')
    );

    // Assert the method was called
    expect($fake)->toHaveCalledStripeMethod('customers.create');

    // Or with enum
    expect($fake)->toHaveCalledStripeMethod(StripeMethod::CustomersCreate);
});
```

### Assert Method Was Not Called

```php
test('does not delete customer', function () {
    $fake = Stripe::fake([
        'customers.create' => StripeFixtures::customer(),
    ]);

    Stripe::customers()->create(
        Stripe::customer(email: 'test@example.com')
    );

    // Assert delete was never called
    expect($fake)->toNotHaveCalledStripeMethod('customers.delete');
});
```

### Assert Call Count

```php
test('creates multiple customers', function () {
    $fake = Stripe::fake([
        'customers.create' => StripeFixtures::customer(),
    ]);

    Stripe::customers()->create(Stripe::customer(email: 'user1@example.com'));
    Stripe::customers()->create(Stripe::customer(email: 'user2@example.com'));
    Stripe::customers()->create(Stripe::customer(email: 'user3@example.com'));

    // Assert called exactly 3 times
    expect($fake)->toHaveCalledStripeMethodTimes('customers.create', 3);
});
```

### Assert With Specific Parameters

```php
test('creates customer with correct email', function () {
    $fake = Stripe::fake([
        'customers.create' => StripeFixtures::customer(),
    ]);

    Stripe::customers()->create(
        Stripe::customer(email: 'test@example.com', name: 'Test User')
    );

    // Get the captured parameters
    $params = $fake->getCall('customers.create');

    // Assert specific values
    expect($params)->toHaveKey('email');
    expect($params['email'])->toBe('test@example.com');
    expect($params['name'])->toBe('Test User');
});
```

## Testing Error Scenarios

Test how your code handles Stripe errors:

```php
use Stripe\Exception\InvalidRequestException;

test('handles missing customer gracefully', function () {
    Stripe::fake([
        'customers.retrieve' => function () {
            throw new InvalidRequestException('No such customer');
        },
    ]);

    expect(fn() => Stripe::customers()->get('cus_missing'))
        ->toThrow(InvalidRequestException::class);
});
```

## Common Testing Patterns

### Test Create Operation

```php
test('can create a product', function () {
    $fake = Stripe::fake([
        'products.create' => StripeFixtures::product([
            'id' => 'prod_test',
            'name' => 'Test Product',
        ]),
    ]);

    $product = Stripe::products()->create(
        Stripe::product(name: 'Test Product')
    );

    expect($product->id)->toBe('prod_test');
    expect($product->name)->toBe('Test Product');
    expect($fake)->toHaveCalledStripeMethod('products.create');
});
```

### Test Retrieve Operation

```php
test('can retrieve a customer', function () {
    Stripe::fake([
        'customers.retrieve' => StripeFixtures::customer([
            'id' => 'cus_123',
            'email' => 'test@example.com',
        ]),
    ]);

    $customer = Stripe::customers()->get('cus_123');

    expect($customer)->toBeInstanceOf(StripeCustomer::class);
    expect($customer->id)->toBe('cus_123');
    expect($customer->email)->toBe('test@example.com');
});
```

### Test Update Operation

```php
test('can update customer name', function () {
    $fake = Stripe::fake([
        'customers.update' => StripeFixtures::customer([
            'id' => 'cus_123',
            'name' => 'Updated Name',
        ]),
    ]);

    $customer = Stripe::customers()->update(
        'cus_123',
        Stripe::customer(name: 'Updated Name')
    );

    expect($customer->name)->toBe('Updated Name');

    // Verify only name was sent
    $params = $fake->getCall('customers.update');
    expect($params)->toHaveKey('name');
    expect($params)->not->toHaveKey('email'); // Not sent
});
```

### Test List Operation

```php
test('can list products', function () {
    Stripe::fake([
        'products.all' => StripeFixtures::productList([
            StripeFixtures::product(['id' => 'prod_1']),
            StripeFixtures::product(['id' => 'prod_2']),
            StripeFixtures::product(['id' => 'prod_3']),
        ]),
    ]);

    $products = Stripe::products()->list();

    expect($products)->toHaveCount(3);
    expect($products->first()->id)->toBe('prod_1');
});
```

### Test Delete Operation

```php
test('can delete a product', function () {
    $fake = Stripe::fake([
        'products.delete' => StripeFixtures::deleted('prod_123', 'product'),
    ]);

    $deleted = Stripe::products()->delete('prod_123');

    expect($deleted)->toBeTrue();
    expect($fake)->toHaveCalledStripeMethod('products.delete');
});
```

## Integration Test Pattern

Test complete workflows:

```php
test('can create complete subscription', function () {
    Stripe::fake([
        'customers.create' => StripeFixtures::customer(['id' => 'cus_123']),
        'products.create' => StripeFixtures::product(['id' => 'prod_123']),
        'prices.create' => StripeFixtures::price([
            'id' => 'price_123',
            'product' => 'prod_123',
        ]),
        'subscriptions.create' => StripeFixtures::subscription([
            'id' => 'sub_123',
            'customer' => 'cus_123',
        ]),
    ]);

    // Create customer
    $customer = Stripe::customers()->create(
        Stripe::customer(email: 'test@example.com')
    );

    // Create product
    $product = Stripe::products()->create(
        Stripe::product(name: 'Premium Plan')
    );

    // Create price
    $price = Stripe::prices()->create(
        Stripe::price(
            product: $product->id,
            unitAmount: 2000,
            currency: 'usd'
        )
    );

    // Create subscription
    $subscription = Stripe::subscriptions()->create(
        Stripe::subscription(
            customer: $customer->id,
            items: [['price' => $price->id]]
        )
    );

    expect($subscription->id)->toBe('sub_123');
    expect($subscription->customer)->toBe('cus_123');
});
```

## Best Practices

### 1. Use Fixtures, Not Raw Arrays

```php
// Good
Stripe::fake([
    'customers.create' => StripeFixtures::customer(['id' => 'cus_123']),
]);

// Bad
Stripe::fake([
    'customers.create' => [
        'id' => 'cus_123',
        'object' => 'customer',
        // Missing many required fields...
    ],
]);
```

### 2. Test Behavior, Not Implementation

```php
// Good - tests behavior
test('sends welcome email after customer creation', function () {
    Stripe::fake(['customers.create' => StripeFixtures::customer()]);

    Mail::fake();

    createCustomer('test@example.com');

    Mail::assertSent(WelcomeEmail::class);
});

// Bad - tests implementation details
test('calls customer create method', function () {
    $fake = Stripe::fake(['customers.create' => StripeFixtures::customer()]);

    createCustomer('test@example.com');

    expect($fake)->toHaveCalledStripeMethod('customers.create');
    // This tests that we called Stripe, not that the business logic works
});
```

### 3. Keep Tests Focused

```php
// Good - one behavior per test
test('creates customer with email', function () {
    Stripe::fake(['customers.create' => StripeFixtures::customer()]);
    $customer = Stripe::customers()->create(Stripe::customer(email: 'test@example.com'));
    expect($customer->email)->toBe('test@example.com');
});

test('creates customer with name', function () {
    Stripe::fake(['customers.create' => StripeFixtures::customer()]);
    $customer = Stripe::customers()->create(Stripe::customer(name: 'Test User'));
    expect($customer->name)->toBe('Test User');
});

// Bad - testing too much at once
test('creates customer with all fields', function () {
    // Tests email, name, phone, address all in one test
    // Hard to debug when it fails
});
```

### 4. Use Descriptive Fixture Overrides

```php
// Good - clear what makes this customer special
StripeFixtures::customer([
    'id' => 'cus_premium_member',
    'email' => 'premium@example.com',
])

// Bad - unclear why these specific values
StripeFixtures::customer([
    'id' => 'cus_123',
    'email' => 'test@example.com',
])
```

## Summary

The fake client system provides:

- **Speed** - No network calls
- **Reliability** - Deterministic responses
- **Flexibility** - Dynamic responses via callables
- **Safety** - No test data in production Stripe
- **Simplicity** - Laravel-style fake API

Testing Stripe integrations is now as easy as testing any other Laravel component.
