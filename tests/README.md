# Testing with Fake Stripe Client

This testing infrastructure provides a Laravel `Http::fake()`-like experience for Stripe API testing.

## Quick Start

```php
use EncoreDigitalGroup\Common\Stripe\Stripe;
use EncoreDigitalGroup\Common\Stripe\Services\StripeCustomerService;
use Tests\Support\StripeFixtures;
use Tests\Support\StripeMethod;

test("creates a stripe customer", function () {
    // Fake the Stripe API using enums for type safety
    $fake = Stripe::fake([
        StripeMethod::CustomersCreate->value => StripeFixtures::customer([
            "id" => "cus_test123",
            "email" => "john@example.com",
        ]),
    ]);

    // Or use strings directly
    // 'customers.create' => StripeFixtures::customer([...])

    // Use your service as normal
    $service = StripeCustomerService::make();
    $customer = $service->create(StripeCustomer::make(email: "john@example.com"));

    // Assert using enums or strings
    expect($customer->id)->toBe("cus_test123");
    expect($fake)->toHaveCalledStripeMethod(StripeMethod::CustomersCreate);
});
```

## Features

### 1. Fake Stripe Responses

Use the `Stripe::fake()` helper to register fake responses for Stripe API methods:

```php
$fake = Stripe::fake([
    "customers.create" => StripeFixtures::customer(),
    "customers.retrieve" => StripeFixtures::customer(["id" => "cus_existing"]),
    "customers.update" => StripeFixtures::customer(["email" => "updated@example.com"]),
    "customers.delete" => StripeFixtures::deleted("cus_123"),
]);
```

The format is `"{service}.{method}" => {response}`.

### 2. Use Fixtures

The `StripeFixtures` class provides realistic test data for common Stripe objects:

```php
// Customer
StripeFixtures::customer(["email" => "test@example.com"]);

// Product
StripeFixtures::product(["name" => "My Product"]);

// Price
StripeFixtures::price(["unit_amount" => 2000]);

// Subscription
StripeFixtures::subscription(["status" => "active"]);

// Bank Account
StripeFixtures::bankAccount(["last4" => "1234"]);

// Financial Connections Account
StripeFixtures::financialConnectionsAccount();

// Deleted Object
StripeFixtures::deleted("cus_123", "customer");

// Error Response
StripeFixtures::error("card_error", "Card declined");
```

All fixtures accept an array of overrides to customize the response.

### 3. Dynamic Responses with Callables

For more complex scenarios, use callables that receive the request parameters:

```php
$fake = Stripe::fake([
    "customers.create" => function ($params) {
        return StripeFixtures::customer([
            "id" => "cus_dynamic",
            "email" => $params["email"] ?? "default@example.com",
            "name" => $params["name"] ?? "Default Name",
        ]);
    },
]);
```

### 4. Wildcard Patterns

Use wildcards to match multiple methods:

```php
$fake = Stripe::fake([
    "customers.*" => StripeFixtures::customer(),  // Matches any customer method
    "subscriptions.*" => StripeFixtures::subscription(),
]);
```

### 5. Custom Pest Expectations

The fake client integrates with Pest's expectation API for fluent assertions:

```php
// Assert a method was called
expect($fake)->toHaveCalledStripeMethod("customers.create");

// Assert with specific parameters
expect($fake)->toHaveCalledStripeMethod("customers.create", ["email" => "test@example.com"]);

// Assert a method was NOT called
expect($fake)->toNotHaveCalledStripeMethod("customers.delete");

// Assert call count
expect($fake)->toHaveCalledStripeMethodTimes("customers.create", 3);

// Chain multiple expectations
expect($fake)
    ->toHaveCalledStripeMethod("customers.create")
    ->toNotHaveCalledStripeMethod("customers.delete");
```

You can also access lower-level methods directly:

```php
// Check if a method was called
if ($fake->wasCalled("customers.update")) {
    // ...
}

// Get call count
$count = $fake->callCount("customers.create");

// Get the parameters from a specific call
$params = $fake->getCall("customers.create", 0); // 0 = first call

// Get all recorded calls
$allCalls = $fake->recorded();

// Clear recorded calls
$fake->clearRecorded();
```

## Available Services

The following Stripe services are supported:

- `customers.*` - Customer operations
- `products.*` - Product operations
- `prices.*` - Price operations
- `subscriptions.*` - Subscription operations
- `paymentMethods.*` - Payment method operations
- `invoices.*` - Invoice operations
- Any other Stripe service follows the same pattern

## Common Methods

Common CRUD operations you can fake:

- `{service}.create` - Create a resource
- `{service}.retrieve` - Retrieve a resource by ID
- `{service}.update` - Update a resource
- `{service}.delete` - Delete a resource
- `{service}.all` - List resources
- `{service}.search` - Search resources

## Examples

### Example 1: Create and Retrieve

```php
test("can create and retrieve customer", function () {
    $fake = Stripe::fake([
        "customers.create" => StripeFixtures::customer(["id" => "cus_new"]),
        "customers.retrieve" => StripeFixtures::customer(["id" => "cus_new"]),
    ]);

    $service = StripeCustomerService::make();
    $created = $service->create(StripeCustomer::make());
    $retrieved = $service->get("cus_new");

    expect($created->id)->toBe("cus_new");
    expect($retrieved->id)->toBe("cus_new");
});
```

### Example 2: List with Multiple Items

```php
test("lists all customers", function () {
    $fake = Stripe::fake([
        "customers.all" => StripeFixtures::customerList([
            StripeFixtures::customer(["id" => "cus_1"]),
            StripeFixtures::customer(["id" => "cus_2"]),
            StripeFixtures::customer(["id" => "cus_3"]),
        ]),
    ]);

    $service = StripeCustomerService::make();
    $customers = $service->list();

    expect($customers)->toHaveCount(3);
});
```

### Example 3: Testing Error Handling

```php
test("handles stripe errors gracefully", function () {
    $fake = Stripe::fake([
        "customers.create" => function () {
            throw new \Stripe\Exception\CardException(
                "Your card was declined.",
                null,
                "card_declined"
            );
        },
    ]);

    $service = StripeCustomerService::make();

    expect(fn () => $service->create(StripeCustomer::make()))
        ->toThrow(\Stripe\Exception\CardException::class);
});
```

### Example 4: Verify Parameters

```php
test("sends correct parameters to stripe", function () {
    $fake = Stripe::fake([
        "customers.create" => StripeFixtures::customer(),
    ]);

    $customer = StripeCustomer::make(
        email: "test@example.com",
        name: "Test User"
    );

    $service = StripeCustomerService::make();
    $service->create($customer);

    // Verify the method was called with specific params
    expect($fake)->toHaveCalledStripeMethod("customers.create", [
        "email" => "test@example.com",
        "name" => "Test User",
    ]);

    // Or inspect the params manually
    $params = $fake->getCall("customers.create", 0);
    expect($params["email"])->toBe("test@example.com");
    expect($params["name"])->toBe("Test User");
});
```

## Architecture

The testing infrastructure consists of:

1. **FakeStripeClient** (`tests/Support/FakeStripeClient.php`) - Extends `Stripe\StripeClient` to intercept API calls
2. **FakeStripeService** (`tests/Support/FakeStripeService.php`) - Handles individual service method calls
3. **StripeFixtures** (`tests/Support/StripeFixtures.php`) - Provides realistic test data
4. **Helper Functions** (`tests/Pest.php`) - Global helpers like `Stripe::fake()`

The `HasStripe` trait has been updated to support dependency injection, allowing the fake client to be injected through Laravel"s service container.

## Tips

1. Always register fakes for the exact methods you"ll be calling
2. Use `StripeFixtures` to generate realistic data instead of writing arrays manually
3. Use wildcards (`*`) when you want to fake multiple methods with the same response
4. Use callables when you need dynamic responses based on input parameters
5. Always assert that the expected methods were called
6. Remember to clear recorded calls between tests if needed using `$fake->clearRecorded()`

## No Network Requests

The fake client makes **zero network requests** to Stripe. All responses are returned immediately from your configured fakes. This makes your tests:

- **Fast** - No network latency
- **Reliable** - No flaky tests due to network issues
- **Isolated** - No external dependencies