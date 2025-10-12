# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Repository Overview

This is a Laravel library that provides a clean, type-safe PHP interface for the Stripe API. It wraps the Stripe PHP SDK with strongly-typed objects, service classes, and
enums while maintaining compatibility with Laravel 11-12 applications.

**Key characteristics:**

- PHP 8.3+ with strict typing
- PHPStan Level 8 static analysis
- Cognitive complexity limits (function: 10, class: 50)
- Pest v4 for testing with custom Stripe faking infrastructure
- Uses `encoredigitalgroup/stdlib` for utilities like `Arr::whereNotNull()`

## Coding Rules

Always follow these rules when writing code:

- Casing rules:
    - Variables are always camelCase
    - Functions are always camelCase
    - Paths are always PascalCase
    - Classes are always PascalCase
    - Enum cases are always PascalCase
- Comments and docblocks:
    - Only add comments when they add real value. Comments should always describe *why* not *what*
    - Only add minimal docblocks to help improve code intelligence and static analysis
- Never use `private`, `final`, or `readonly` keywords. If they are needed, the developer will implement them.
- Avoid magic strings when an enum or a const is an option. Look in the existing codebase for an enum—it'll often be there
- Avoid variables if possible. eg. rather than calling `$response = $this->get(...)` followed by `assetsRedirect()`, just chain the calls
- Use the early return pattern where possible
- Prefer arrow functions when the line will stay under 80-100 chars
- Use double quotes instead of single quotes.
- Testing:
    - All tests should be written using PestPHP. Related tests should be grouped by `describe()` blocks.
    - All tests should be written using the `test()` function instead of the `it()` function.
    - Avoid tests that are largely testing that the code behaves specifically as it is written, and instead test the intention. eg. a validation message may change over
      time, but the invalid input should not be allowed regardless.
    - When calling eloquent factories:
        - Prefer named factory methods over `state` or passing values to `create` where possible
        - Only factory data that is relevant to the test--favor defaults otherwise
        - Eloquent factories should create necessary relationships implicitly. If you don't need a relation for the test, let the factory create it rather than creating
          it in the test.
- If there are "todo" comments that need to be resolved before the code gets merged, use `// FIXME`
- Prefer `App::bound()` over `app()->bound()` but prefer `app()` over `App::make()`

## Commands

### Testing

```bash
# Run all tests
./vendor/bin/pest

# Run specific test suite
./vendor/bin/pest tests/Feature/
./vendor/bin/pest tests/Unit/

# Run with coverage
./vendor/bin/pest --coverage --min=80

# Run single test file
./vendor/bin/pest tests/Feature/StripeCustomerServiceTest.php

# Stop on first failure
./vendor/bin/pest --stop-on-failure
```

### Code Quality

```bash
# Static analysis (Level 8)
./vendor/bin/phpstan analyse

# Code style fixing (uses Duster which wraps PHP-CS-Fixer)
./vendor/bin/duster fix

# Refactoring (uses Rector)
./vendor/bin/rector process
```

## Architecture

### Service Layer Pattern

All Stripe interactions follow a consistent service pattern:

1. **Services** (`src/php/Services/`) - Use the `HasStripe` trait which provides:
    - Automatic Stripe client initialization via `__construct()`
    - Container-aware client resolution (checks Laravel container first)
    - Static `make()` factory method
    - Dependency injection support for testing

2. **DTOs** (`src/php/Objects/`) - Immutable data objects with:
    - `make()` static factory for named parameters
    - `fromStripeObject()` to convert from Stripe SDK objects
    - `toArray()` for API requests (filters null values via `Arr::whereNotNull()`)
    - All properties are nullable and use camelCase (converted to snake_case in `toArray()`)

3. **Enums** (`src/php/Enums/`) - String-backed enums for Stripe constants:
    - All values match Stripe's exact API values (e.g., `RecurringInterval::Month->value === 'month'`)
    - Enum names use PascalCase (e.g., `SubscriptionStatus::Active`)

### HasStripe Trait Pattern

The `HasStripe` trait implements a dependency injection hierarchy:

```php
public function __construct(?StripeClient $client = null)
{
    // 1. Direct injection (highest priority - used for testing)
    if ($client instanceof \Stripe\StripeClient) {
        $this->stripe = $client;
        return;
    }

    // 2. Laravel container resolution
    if (function_exists("app") && App::bound(StripeClient::class)) {
        $this->stripe = app(StripeClient::class);
        return;
    }

    // 3. New client from config (fallback)
    $config = self::config();
    $this->stripe = new StripeClient($config->authentication->secretKey);
}
```

**Important:** Use `App::bound()` for checking bindings but `app()` for resolution.

### Object Conversion Pattern

All DTOs follow this conversion pattern:

```php
// Stripe SDK -> DTO
public static function fromStripeObject(ApiObject $obj): self
{
    // Extract nested objects/enums
    // Handle both string IDs and nested objects for relations
    // Convert API objects to our DTOs
    return self::make(...);
}

// DTO -> API array
public function toArray(): array
{
    $array = [
        'field_name' => $this->fieldName,  // camelCase -> snake_case
        'enum_field' => $this->enumField?->value,  // Enum -> string
        'nested' => $this->nested?->toArray(),  // Nested DTO -> array
    ];

    return Arr::whereNotNull($array);  // Filter null values
}
```

**Key patterns:**

- Always handle nullable properties with `?->` operator
- Stripe SDK returns both string IDs and nested objects for relations - handle both:
  ```php
  customer: is_string($obj->customer) ? $obj->customer : $obj->customer->id
  ```
- Enums are extracted from nested `StripeObject` properties with type checking
- Package dimensions, addresses, and metadata require special handling

### Service Method Cleanup Pattern

Service methods that create/update resources must remove read-only fields:

```php
public function create(StripeProduct $product): StripeProduct
{
    $data = $product->toArray();

    // Remove fields that can't be sent on create
    unset($data["id"], $data["created"], $data["updated"]);

    $stripeProduct = $this->stripe->products->create($data);
    return StripeProduct::fromStripeObject($stripeProduct);
}
```

## Testing Infrastructure

### Stripe::fake() Pattern

Tests use a custom faking system that intercepts Stripe API calls without network requests:

```php
use EncoreDigitalGroup\Common\Stripe\Stripe;
use Tests\Support\StripeFixtures;
use Tests\Support\StripeMethod;

test('example', function () {
    // Setup fake - binds to Laravel container
    $fake = Stripe::fake([
        StripeMethod::CustomersCreate->value => StripeFixtures::customer([
            'id' => 'cus_test123',
            'email' => 'test@example.com',
        ]),
    ]);

    // Use service normally - automatically uses fake
    $service = StripeCustomerService::make();
    $result = $service->create(StripeCustomer::make(email: 'test@example.com'));

    // Assert
    expect($result->id)->toBe('cus_test123');
    expect($fake)->toHaveCalledStripeMethod(StripeMethod::CustomersCreate);
});
```

**Key components:**

- `FakeStripeClient` - Extends Stripe SDK client, intercepts `__get()` for service access
- `FakeStripeService` - Handles method calls, records params
- `StripeFixtures` - Provides realistic test data (use these instead of raw arrays)
- `StripeMethod` enum - Type-safe method names (PascalCase, e.g., `CustomersCreate`)

**Custom Pest expectations:**

- `toHaveCalledStripeMethod(string|BackedEnum $method, ?array $params = null)`
- `toNotHaveCalledStripeMethod(string|BackedEnum $method)`
- `toHaveCalledStripeMethodTimes(string|BackedEnum $method, int $count)`

**Test organization:**

- `tests/Feature/` - Service integration tests (extends `Tests\TestCase`)
- `tests/Unit/` - DTO unit tests (extends `Tests\TestCase`)
- `tests/Support/` - Test infrastructure (FakeStripeClient, StripeFixtures, etc.)

### Fixture Usage

Always use `StripeFixtures` for test data:

```php
// Good
$fake = Stripe::fake([
    'customers.create' => StripeFixtures::customer(['id' => 'cus_123']),
    'products.all' => StripeFixtures::productList([
        StripeFixtures::product(['id' => 'prod_1']),
        StripeFixtures::product(['id' => 'prod_2']),
    ]),
]);

// Bad - don't construct raw arrays
$fake = Stripe::fake([
    'customers.create' => ['id' => 'cus_123', 'object' => 'customer', ...],
]);
```

### Wildcard and Callable Patterns

```php
// Wildcard - matches any customer method
Stripe::fake(['customers.*' => StripeFixtures::customer()]);

// Callable - dynamic responses based on params
Stripe::fake([
    'customers.create' => function ($params) {
        return StripeFixtures::customer([
            'email' => $params['email'] ?? 'default@example.com',
        ]);
    },
]);
```

## Code Style Notes

1. **Prefer App facade over app() function for bound checks:**
   ```php
   // Correct
   if (App::bound(StripeClient::class)) {
       $client = app(StripeClient::class);
   }

   // Incorrect
   if (app()->bound(StripeClient::class)) {
       $client = app(StripeClient::class);
   }
   ```

2. **Cognitive complexity:** Functions must stay under complexity of 10. Break complex methods into smaller private methods.

3. **PHPStan compliance:** Code must pass Level 8 analysis. Common issues:
    - Array param types for Stripe SDK methods (often too wide)
    - Missing type hints on closures
    - Dynamic property access on StripeObject (use `property_exists()` checks)

4. **Enum naming:** All enum cases use PascalCase (not SCREAMING_SNAKE_CASE):
   ```php
   // Correct
   StripeMethod::CustomersCreate
   SubscriptionStatus::Active

   // Incorrect
   StripeMethod::CUSTOMERS_CREATE
   SubscriptionStatus::ACTIVE
   ```

5. **Test expectations:** Chain expectations for cleaner test code:
   ```php
   // Preferred
   expect($result)
       ->toBeInstanceOf(StripeCustomer::class)
       ->and($result->id)->toBe('cus_123')
       ->and($fake)->toHaveCalledStripeMethod(StripeMethod::CustomersCreate);
   ```

6. **Namespace consistency:** Use full namespace paths in `use` statements, never use aliases unless required for conflicts.

## Important Patterns

### Converting Nested Stripe Objects

```php
// Extract nested address from Stripe customer
$address = null;
if ($stripeCustomer->address) {
    /** @var \Stripe\StripeObject $stripeAddress */
    $stripeAddress = $stripeCustomer->address;
    $address = StripeAddress::make(
        line1: $stripeAddress->line1 ?? null,
        city: $stripeAddress->city ?? null,
        // ...
    );
}
```

### Handling Recurring/Tiers Arrays

Price objects contain complex nested arrays that need careful enum conversion:

```php
// In fromStripeObject()
$recurring = null;
if ($stripePrice->recurring) {
    $recurringObj = $stripePrice->recurring;
    $recurring = [
        'interval' => property_exists($recurringObj, 'interval') && $recurringObj->interval
            ? RecurringInterval::from($recurringObj->interval)
            : null,
        'interval_count' => $recurringObj->interval_count ?? null,
    ];
}

// In toArray()
if ($this->recurring !== null) {
    $recurring = [
        'interval' => $this->recurring['interval'] instanceof RecurringInterval
            ? $this->recurring['interval']->value
            : ($this->recurring['interval'] ?? null),
    ];
    $recurring = Arr::whereNotNull($recurring);
}
```

### Service Archive/Reactivate Pattern

Products and Prices use soft-delete pattern:

```php
public function archive(string $productId): StripeProduct
{
    $stripeProduct = $this->stripe->products->update($productId, [
        "active" => false,
    ]);

    return StripeProduct::fromStripeObject($stripeProduct);
}

public function reactivate(string $productId): StripeProduct
{
    $stripeProduct = $this->stripe->products->update($productId, [
        "active" => true,
    ]);

    return StripeProduct::fromStripeObject($stripeProduct);
}
```

Note: Prices can only be archived (not deleted) per Stripe API limitations.

## Common Gotchas

1. **Metadata is always a StripeObject** - Call `->toArray()` when converting:
   ```php
   metadata: $stripeCustomer->metadata->toArray()
   ```

2. **Price tiers property** - May not exist on Price objects, always check with `property_exists()` or null coalescing.

3. **Test isolation** - `Stripe::fake()` binds to container as singleton. Container is reset between tests by TestCase.

4. **File structure** - Source is in `src/php/`, not just `src/`. Namespaces reflect this: `EncoreDigitalGroup\Common\Stripe\`.

5. **Stripe SDK differences** - Package supports both v16.5+ and v17.0+ which may have minor API differences.
