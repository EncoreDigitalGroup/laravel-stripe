# Getting Started

This guide will take you from installation to your first successful API call, introducing the opinionated core concepts that make this library
different from working directly with the Stripe SDK.

## Installation

Install the library via Composer:

```bash
composer require encoredigitalgroup/stripe
```

The package will automatically register its service provider with Laravel"s auto-discovery feature.

## Configuration

Add your Stripe secret key to your `.env` file:

```env
STRIPE_SECRET_KEY=sk_test_51abc...
```

That"s it! The library will automatically use this key for all API calls. For more advanced configuration options, see
the [Architecture documentation](06-architecture.md#configuration).

## Your First API Call

Let"s create a simple customer to understand how this library works:

```php
use EncoreDigitalGroup\Stripe\Stripe;

$customerData = Stripe::customer(email: "john@example.com", name: "John Doe");

// Create a customer
$customer = Stripe::customers()->create($customerData);

// The response is a fully-typed object
echo $customer->id;    // "cus_abc123..."
echo $customer->email; // "john@example.com"
echo $customer->name;  // "John Doe"
```

## Core Concepts

### 1. Services as API Gateways

Every supported Stripe resource has a corresponding service accessible via the Stripe facade. It"s important to note that this is not a Laravel facade.

- `Stripe::customers()` - Customer management
- `Stripe::products()` - Product management
- `Stripe::prices()` - Price management
- `Stripe::subscriptions()` - Subscription management

Services provide clean, typed methods that correspond to Stripe API operations:

```php
$customer = Stripe::customers()->create($customerData);     // POST /customers
$customer = Stripe::customers()->get("cus_123");           // GET /customers/cus_123
$customer = Stripe::customers()->update("cus_123", $data); // POST /customers/cus_123
$deleted = Stripe::customers()->delete("cus_123");         // DELETE /customers/cus_123
$customers = Stripe::customers()->list(["limit" => 10]);   // GET /customers
$results = Stripe::customers()->search("email:john@example.com"); // GET /customers/search
```

### 2. DTOs (Data Transfer Objects)

Instead of working with dynamic arrays or Stripe's generic objects, this library provides strongly-typed DTOs:

```php
// Instead of this (Stripe SDK):
$customer = $stripe->customers->create([
    "email" => "john@example.com",
    "name" => "John Doe",
    "address" => [
        "line1" => "123 Main St",
        "city" => "Anytown",
        "state" => "CA",
        "postal_code" => "12345",
        "country" => "US"
    ]
]);

// You write this (Laravel Stripe):
$customer = Stripe::customers()->create(Stripe::customer(
    email: "john@example.com",
    name: "John Doe",
    address: Stripe::address(
        line1: "123 Main St",
        city: "Anytown",
        state: "CA",
        postalCode: "12345",
        country: "US"
    )
));
```

### 3. Enums for Type Safety

The library provides enums for Stripe constants, preventing typos and providing IDE autocompletion:

```php
use EncoreDigitalGroup\Stripe\Enums\{PriceType, RecurringInterval};

$price = Stripe::price(
    currency: "usd",
    unitAmount: 2000,
    type: PriceType::Recurring,  // Not "recurring"
    recurring: [
        "interval" => RecurringInterval::Month  // Not "month"
    ]
);
```

### 4. Automatic Conversion

The library automatically converts between Stripe SDK objects and our DTOs:

```php
// When you call a service method, three conversions happen:

// 1. DTO → Array (for Stripe SDK)
$data = $customerData->toArray();

// 2. Array → Stripe API → Stripe Object (SDK handles this)
$stripeCustomer = $this->stripe->customers->create($data);

// 3. Stripe Object → DTO (our library)
return StripeCustomer::fromStripeObject($stripeCustomer);
```

## Understanding the Factory Pattern

You"ll see `Stripe::customer()`, `Stripe::product()`, etc. throughout this library. These are factory methods that provide named parameters and better IDE support:

```php
// Traditional constructor (works, but verbose)
$customer = new StripeCustomer(
    id: null,
    address: null,
    description: null,
    email: "john@example.com",
    name: "John Doe",
    phone: null,
    shipping: null
);

// Factory pattern (cleaner, skip null values)
$customer = Stripe::customer(
    email: "john@example.com",
    name: "John Doe"
);
```

## Error Handling

The library throws the same exceptions as the Stripe SDK:

```php
use Stripe\Exception\ApiErrorException;

try {
    $customer = Stripe::customers()->create($customerData);
} catch (ApiErrorException $e) {
    // Handle Stripe API errors
    echo "Stripe error: " . $e->getMessage();
}
```

## Working with Collections

List methods return Laravel Collections with full type safety:

```php
$customers = Stripe::customers()->list(["limit" => 50]);

// This is a Collection<int, StripeCustomer>
$customers->filter(fn($customer) => str_contains($customer->email, "@gmail.com"))
          ->map(fn($customer) => $customer->name)
          ->values();
```

## Next Steps

Now that you understand the basics, let"s dive into specific resources:

- **[Customers](02-customers.md)** - Customer management, addresses, and shipping
- **[Products](03-products.md)** - Product creation and lifecycle management
- **[Prices](04-prices.md)** - Complex pricing including recurring billing and tiers
- **[Testing](05-testing.md)** - How to test your Stripe integration

Or explore the architectural patterns:

- **[Architecture](06-architecture.md)** - Deep dive into library design and extensibility

## Quick Reference

### Common Service Patterns

```php
// All services follow this pattern:
$service = Stripe::customers(); // or products(), prices(), subscriptions()

// CRUD operations:
$entity = $service->create($dto);
$entity = $service->get($id);
$entity = $service->update($id, $dto);
$deleted = $service->delete($id);
$collection = $service->list($params);
```

### Common DTO Patterns

```php
// All DTOs support:
$dto = Stripe::customer(/* named params */); // or product(), price(), etc.
$array = $dto->toArray();
$dto = DtoClass::fromStripeObject($stripeObject);
```

### Environment Variables

```env
# Required
STRIPE_SECRET_KEY=sk_test_...

# Optional (defaults to test mode)
STRIPE_PUBLISHABLE_KEY=pk_test_...
```

Ready to start building? Let"s explore **[customer management](02-customers.md)** next!