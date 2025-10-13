# Architecture Overview

Understanding the architecture of this library will help you use it effectively and debug issues if they arise.

## Design Philosophy

The library is built on three architectural principles:

### 1. Single Entry Point

All user-facing interactions go through the `Stripe` class. While the library contains many internal classes (services, DTOs, enums), these are implementation
details providing logical separation. The Stripe class shields you from implementation complexity.

### 2. Immutable Data Objects

All Stripe API objects are represented as immutable DTOs (Data Transfer Objects). These objects have public readonly properties and can only be created through factory
methods.

### 3. Type Safety Through Enums

Stripe constants (like `'active'`, `'month'`, `'trialing'`) are represented as PHP enums. This eliminates magic strings.

## Component Layers

```
┌─────────────────────────────────────┐
│      Stripe Class (Entry Point)     │  ← Your code interacts here
├─────────────────────────────────────┤
│      Service Layer                  │  ← Handles API calls
├─────────────────────────────────────┤
│      DTO Layer (Data Objects)       │  ← Type-safe data structures
├─────────────────────────────────────┤
│      Enum Layer (Constants)         │  ← Type-safe constants
├─────────────────────────────────────┤
│      Stripe PHP SDK                 │  ← Official Stripe client
└─────────────────────────────────────┘
```

### Facade Layer

The `Stripe` class provides two types of methods:

1. **Factory methods** - Create data objects without talking to Stripe
2. **Service accessors** - Get service instances that make API calls

```php
// Factory methods (no API call)
$customer = Stripe::customer(email: 'test@example.com');
$product = Stripe::product(name: 'My Product');

// Service accessors (returns service instance)
$customerService = Stripe::customers();
$productService = Stripe::products();

// Chain them together
$result = Stripe::customers()->create(
    Stripe::customer(email: 'test@example.com')
);
```

### Service Layer

Services handle all Stripe API interactions. Each service follows a consistent pattern:

- **Create**: `create(DTO) -> DTO`
- **Retrieve**: `get(string $id) -> DTO`
- **Update**: `update(string $id, DTO) -> DTO`
- **Delete**: `delete(string $id) -> bool`
- **List**: `list(array $params) -> Collection<DTO>`

Services automatically:

- Initialize the Stripe client
- Convert DTOs to API request arrays
- Convert API responses back to DTOs
- Filter out null values
- Remove read-only fields before sending updates

**Why services are internal**: They contain boilerplate code for API communication. The Stripe class provides a cleaner interface.

### DTO Layer

Data Transfer Objects represent Stripe API objects. They follow a specific pattern:

1. **Construction**: Use the `make()` static factory method
2. **From API**: Use `fromStripeObject()` to convert Stripe SDK objects
3. **To API**: Use `toArray()` to convert to API request format

All properties are nullable because Stripe's API is flexible about which fields are required or returned.

### Enum Layer

Enums provide type-safe predefined values for Stripe's string values:

```php
enum SubscriptionStatus: string {
    case Active = 'active';
    case PastDue = 'past_due';
    case Canceled = 'canceled';
    // ...
}

enum RecurringInterval: string {
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';
    case Year = 'year';
}
```

The enum's `value` property matches Stripe's exact API values. This is crucial for serialization:

```php
// When sending to Stripe
$array['interval'] = RecurringInterval::Month->value; // 'month'

// When receiving from Stripe
$interval = RecurringInterval::from($stripeData['interval']);
```

## Dependency Injection Pattern

The library uses a dependency injection hierarchy for the Stripe client:

1. **Explicit injection** (highest priority) - Used by tests
2. **Laravel container** - Used in normal operation
3. **Configuration fallback** - Used if container isn't available

This is handled by the `HasStripe` trait that all services use:

```php
// Tests can inject a fake client
$service = new StripeCustomerService($fakeClient);

// Laravel resolves from container automatically
$service = Stripe::customers(); // Gets client from container

// Fallback creates new client from config
$service = new StripeCustomerService(); // Creates client from env
```

**Why this matters**: Testing becomes trivial. The `Stripe::fake()` method binds a fake client to Laravel's container, and all services automatically pick it up.

## Data Flow

### Creating an Object

```
Your Code                  Stripe class        Service              Stripe API
   │                         │                    │                     │
   │──Stripe::customer()────→│                    │                     │
   │←──────DTO object────────│                    │                     │
   │                         │                    │                     │
   │──Stripe::customers()───→│                    │                     │
   │←────Service instance────│                    │                     │
   │                         │                    │                     │
   │──service->create(dto)───────────────→│       │                     │
   │                         │            │──toArray()─────────────────→│
   │                         │            │──API call──────────────────→│
   │                         │            │←──API response──────────────│
   │                         │            │←─fromStripeObject()─────────│
   │←─────────────────DTO object──────────│                             │
```

### Retrieving an Object

```
Your Code                 Service              Stripe API
   │                        │                     │
   │──service->get(id)─────→│                     │
   │                        │──API call──────────→│
   │                        │←──API response──────│
   │                        │──fromStripeObject()─→
   │←──────DTO object───────│                     │
```

## Null Handling Philosophy

The library embraces nullable types because:

1. **Stripe's API is flexible** - Most fields are optional
2. **Partial updates are common** - You often update just one field
3. **Read vs Write differ** - Some fields are read-only, others write-only

The `toArray()` method filters out null values automatically, so you can create sparse update objects:

```php
// Only update the name
$updates = Stripe::customer(name: 'New Name');
$customer = Stripe::customers()->update('cus_...', $updates);

// toArray() produces: ['name' => 'New Name']
// NOT: ['name' => 'New Name', 'email' => null, 'phone' => null, ...]
```

## Service Isolation

Each Stripe resource has its own service:

- `StripeCustomerService` - Customer operations
- `StripeProductService` - Product operations
- `StripePriceService` - Price operations
- `StripeSubscriptionService` - Subscription operations

## Laravel Integration

The library integrates with Laravel through:

1. **Service Provider** - Registers the Stripe client in the container
2. **Auto-discovery** - Composer automatically loads the provider
3. **Container bindings** - Services resolve dependencies from the container

## Testing Architecture

The testing system mirrors Laravel's `Http::fake()` pattern:

1. **Fake client** extends the real Stripe client
2. **Method interception** captures and records calls
3. **Response mapping** returns predefined responses
4. **Assertion helpers** verify expected behavior

See the [Testing Guide](testing.md) for detailed information.
