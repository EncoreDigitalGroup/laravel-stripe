# Architecture

This chapter provides a deep dive into the Laravel Stripe library's architecture, design patterns, and extensibility. Understanding the architecture helps you make
informed decisions about customization, troubleshooting, and contributing to the library.

## Table of Contents

- [Design Philosophy](#design-philosophy)
- [Architectural Overview](#architectural-overview)
- [Layer-by-Layer Breakdown](#layer-by-layer-breakdown)
- [Dependency Injection Pattern](#dependency-injection-pattern)
- [Configuration System](#configuration-system)
- [Testing Infrastructure](#testing-infrastructure)
- [Extension Points](#extension-points)
- [Performance Considerations](#performance-considerations)
- [Security Considerations](#security-considerations)

## Design Philosophy

The Laravel Stripe library is built on several core principles that influence every architectural decision:

### 1. Type Safety First

Everything is strongly typed to catch errors at development time, not runtime:

```php
// Instead of this (Stripe SDK):
$customer = $stripe->customers->create([
    'email' => 'test@example.com',
    'metadata' => ['tier' => 'premium']
]);
$email = $customer->email; // Could be null, no IDE support

// We provide this:
$customer = $service->create(StripeCustomer::make(
    email: 'test@example.com',
    metadata: ['tier' => 'premium']
));
$email = $customer->email; // Typed as ?string, full IDE support
```

### 2. Laravel Conventions

Follow Laravel patterns that developers already know:

```php
// Service pattern (like Laravel's built-in services)
$customerService = StripeCustomerService::make();

// Collection pattern (like Eloquent)
$customers = $service->list()->filter(fn($c) => str_contains($c->email, '@gmail.com'));

// Testing pattern (like Http::fake())
Stripe::fake(['customers.create' => StripeFixtures::customer()]);
```

### 3. Immutable Data Objects

DTOs are immutable and conversion-aware:

```php
// Objects don't change after creation
$customer = StripeCustomer::make(email: 'test@example.com');
// $customer->email = 'new@example.com'; // This would cause an error

// But they convert seamlessly
$array = $customer->toArray(); // For API calls
$customer = StripeCustomer::fromStripeObject($stripeResponse); // From API responses
```

### 4. Testability by Design

Every component is designed to be easily testable:

```php
// Services accept dependency injection
$service = new StripeCustomerService($fakeClient);

// Comprehensive faking system
$fake = Stripe::fake([...]);

// Rich assertions
expect($fake)->toHaveCalledStripeMethod('customers.create');
```

## Architectural Overview

The library follows a layered architecture with clear separation of concerns:

```
┌─────────────────────────────────────────────────────────────┐
│                    Application Layer                        │
│  (Your Laravel app using services and DTOs)                 │
├─────────────────────────────────────────────────────────────┤
│                     Service Layer                           │
│  StripeCustomerService, StripeProductService, etc.          │
├─────────────────────────────────────────────────────────────┤
│                      DTO Layer                              │
│  StripeCustomer, StripeProduct, StripePrice, etc.           │
├─────────────────────────────────────────────────────────────┤
│                   Support Layer                             │
│  HasStripe trait, Config, Enums, Testing utilities          │
├─────────────────────────────────────────────────────────────┤
│                 Stripe SDK Layer                            │
│  Official Stripe PHP SDK (StripeClient, etc.)               │
└─────────────────────────────────────────────────────────────┘
```

### Communication Flow

```php
// 1. Application creates DTO
$customer = StripeCustomer::make(email: 'test@example.com');

// 2. Service converts DTO to array
$data = $customer->toArray(); // ['email' => 'test@example.com']

// 3. Service calls Stripe SDK
$stripeCustomer = $this->stripe->customers->create($data);

// 4. Service converts response back to DTO
return StripeCustomer::fromStripeObject($stripeCustomer);
```

## Layer-by-Layer Breakdown

### Service Layer

Services are the primary API for interacting with Stripe. They handle:

- Client initialization and dependency injection
- Data conversion between DTOs and Stripe SDK
- Error handling and response processing
- Business logic specific to each resource type

#### HasStripe Trait

All services use the `HasStripe` trait, which implements a sophisticated dependency injection pattern:

```php
trait HasStripe
{
    public function __construct(?StripeClient $client = null)
    {
        // 1. Direct injection (highest priority - for testing)
        if ($client instanceof \Stripe\StripeClient) {
            $this->stripe = $client;
            return;
        }

        // 2. Laravel container resolution (production)
        if (function_exists("app") && app()->bound(StripeClient::class)) {
            $this->stripe = app(StripeClient::class);
            return;
        }

        // 3. New client from config (fallback)
        $config = self::config();
        $this->stripe = new StripeClient($config->authentication->secretKey);
    }
}
```

This pattern enables:

- **Testing**: Inject fake clients easily
- **Production**: Use Laravel container for shared instances
- **Standalone**: Work outside Laravel with direct configuration

#### Service Factory Pattern

```php
class StripeCustomerService
{
    use HasStripe;

    public static function make(?StripeClient $client = null): static
    {
        return new static($client);
    }
}

// Usage:
$service = StripeCustomerService::make(); // Uses default client
$service = StripeCustomerService::make($fakeClient); // Uses injected client
```

### DTO Layer

Data Transfer Objects provide type-safe representations of Stripe data:

#### HasMake Trait

All DTOs use the `HasMake` trait for consistent factory method pattern:

```php
trait HasMake
{
    public static function make(mixed ...$params): static
    {
        return new static(...$params);
    }
}

// Enables this syntax:
$customer = StripeCustomer::make(
    email: 'test@example.com',
    name: 'Test User'
);
```

#### Conversion Pattern

Every DTO implements two key methods:

```php
class StripeCustomer
{
    // Convert FROM Stripe SDK object TO our DTO
    public static function fromStripeObject(Customer $stripeCustomer): self
    {
        // Handle nested objects, enums, and complex data structures
        return self::make(
            id: $stripeCustomer->id,
            email: $stripeCustomer->email,
            // ... convert all fields with proper type handling
        );
    }

    // Convert FROM our DTO TO array for Stripe API
    public function toArray(): array
    {
        $array = [
            'id' => $this->id,
            'email' => $this->email,
            // ... all fields converted to snake_case
        ];

        // Remove null values (Stripe doesn't want them)
        return Arr::whereNotNull($array);
    }
}
```

#### Field Naming Convention

The library handles the impedance mismatch between PHP and Stripe conventions:

```php
// PHP (camelCase)
$address = StripeAddress::make(postalCode: '12345');

// Stripe API (snake_case)
$array = $address->toArray(); // ['postal_code' => '12345']

// Conversion back
$address = StripeAddress::fromStripeObject($stripeResponse);
echo $address->postalCode; // '12345' (camelCase again)
```

### Support Layer

#### Configuration System

The configuration system is simple but flexible:

```php
class StripeConfig
{
    public Authentication $authentication;

    public static function make(): StripeConfig
    {
        // Singleton pattern for performance
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }
}

class Authentication
{
    public ?string $publicKey = null;
    public ?string $secretKey = null;

    public function __construct()
    {
        // Load from environment
        $this->secretKey = $_ENV['STRIPE_SECRET_KEY'] ?? null;
        $this->publicKey = $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? null;
    }
}
```

#### Enum System

All Stripe constants are represented as type-safe enums:

```php
enum PriceType: string
{
    case OneTime = "one_time";
    case Recurring = "recurring";
}

// Usage:
$price = StripePrice::make(
    type: PriceType::Recurring  // Type-safe, no typos possible
);

// In DTOs:
public function toArray(): array
{
    return [
        'type' => $this->type?->value, // Converts enum to string
        // ...
    ];
}
```

#### Main Entry Point

The `Stripe` class provides a Laravel-style facade:

```php
class Stripe
{
    use HasStripe;

    // Factory methods for DTOs
    public static function customer(mixed ...$params): StripeCustomer
    {
        return StripeCustomer::make(...$params);
    }

    // Service accessor methods
    public static function customers(): StripeCustomerService
    {
        return StripeCustomerService::make();
    }

    // Testing method
    public static function fake(array $fakes = []): FakeStripeClient
    {
        $fake = new FakeStripeClient($fakes);
        if (function_exists("app")) {
            app()->instance(StripeClient::class, $fake);
        }
        return $fake;
    }
}
```

## Dependency Injection Pattern

The library implements a three-tier dependency injection hierarchy that works in testing, Laravel, and standalone environments.

### Tier 1: Direct Injection (Testing)

```php
// Highest priority - used in tests
$fakeClient = new FakeStripeClient();
$service = new StripeCustomerService($fakeClient);
```

### Tier 2: Container Resolution (Laravel)

```php
// Production usage in Laravel
app()->bind(StripeClient::class, function() {
    return new StripeClient(config('stripe.secret_key'));
});

$service = StripeCustomerService::make(); // Gets client from container
```

### Tier 3: Configuration Fallback (Standalone)

```php
// Works outside Laravel
$_ENV['STRIPE_SECRET_KEY'] = 'sk_test_...';
$service = StripeCustomerService::make(); // Creates new client from config
```

### Why This Pattern?

This hierarchy ensures the library works seamlessly across different contexts:

1. **Tests**: Direct injection gives full control over the client
2. **Laravel apps**: Container resolution allows shared instances and configuration
3. **Standalone usage**: Fallback ensures the library works without Laravel

## Configuration System

### Environment-Based Configuration

The library reads configuration from environment variables:

```env
STRIPE_SECRET_KEY=sk_test_...
STRIPE_PUBLISHABLE_KEY=pk_test_...
```

### Lazy Loading

Configuration is loaded only when needed:

```php
// This doesn't load configuration
$service = StripeCustomerService::make();

// This loads configuration (if no client was injected)
$customer = $service->create($customerData);
```

### Extension Points

You can extend the configuration system:

```php
class CustomStripeConfig extends StripeConfig
{
    public string $customSetting;

    public function __construct()
    {
        parent::__construct();
        $this->customSetting = $_ENV['CUSTOM_STRIPE_SETTING'] ?? 'default';
    }
}
```

## Testing Infrastructure

The testing system is built around the fake client pattern:

### FakeStripeClient Architecture

```php
class FakeStripeClient extends StripeClient
{
    protected array $fakes = [];
    protected array $recorded = [];

    public function __get($name): FakeStripeService
    {
        // Intercept service access (e.g., $client->customers)
        return new FakeStripeService($name, $this);
    }

    public function resolveFake(string $method, array $params): mixed
    {
        // Record the call
        $this->recordCall($method, $params);

        // Find matching fake response
        if (isset($this->fakes[$method])) {
            return $this->processResponse($this->fakes[$method], $params);
        }

        // Check for wildcard matches
        $wildcardResponse = $this->findWildcardMatch($method);
        if ($wildcardResponse !== null) {
            return $this->processResponse($wildcardResponse, $params);
        }

        throw new RuntimeException("No fake registered for method [{$method}]");
    }
}
```

### FakeStripeService

```php
class FakeStripeService
{
    public function __call(string $method, array $args): mixed
    {
        $methodName = "{$this->serviceName}.{$method}";
        $params = $args[1] ?? $args[0] ?? [];

        return $this->client->resolveFake($methodName, $params);
    }
}
```

### Wildcard Support

The fake system supports wildcard patterns:

```php
Stripe::fake(['customers.*' => StripeFixtures::customer()]);

// Matches:
// - customers.create
// - customers.retrieve
// - customers.update
// - etc.
```

### Dynamic Responses

Callables enable dynamic responses based on parameters:

```php
Stripe::fake([
    'customers.create' => function(array $params) {
        return StripeFixtures::customer([
            'email' => $params['email'],
            'id' => 'cus_' . uniqid()
        ]);
    }
]);
```

## Extension Points

### Custom Services

Create custom services by extending the base pattern:

```php
class CustomStripeService
{
    use HasStripe;

    public function customOperation(array $data): array
    {
        return $this->stripe->request('post', '/v1/custom_endpoint', $data);
    }
}
```

### Custom DTOs

Create custom DTOs for new Stripe objects:

```php
class CustomStripeObject
{
    use HasMake;

    public function __construct(
        public ?string $id = null,
        public ?string $customField = null
    ) {}

    public static function fromStripeObject($stripeObject): self
    {
        return self::make(
            id: $stripeObject->id,
            customField: $stripeObject->custom_field
        );
    }

    public function toArray(): array
    {
        return Arr::whereNotNull([
            'id' => $this->id,
            'custom_field' => $this->customField
        ]);
    }
}
```

### Custom Enums

Add custom enums for new Stripe constants:

```php
enum CustomEnum: string
{
    case Value1 = "value_1";
    case Value2 = "value_2";
}
```

### Custom Fixtures

Extend the fixtures system:

```php
class CustomStripeFixtures extends StripeFixtures
{
    public static function customObject(array $overrides = []): array
    {
        return array_merge([
            'id' => 'custom_' . self::randomId(),
            'object' => 'custom_object',
            'custom_field' => 'default_value'
        ], $overrides);
    }
}
```

## Performance Considerations

### Client Reuse

The library encourages client reuse through Laravel's container:

```php
// Good: Reuses the same client instance
$customers = Stripe::customers();
$products = Stripe::products();

// Less efficient: Creates new clients
$customers = new StripeCustomerService();
$products = new StripeProductService();
```

### Lazy Loading

Configuration and clients are loaded only when needed:

```php
// No network calls or configuration loading
$service = StripeCustomerService::make();

// Configuration loaded here (if needed)
$customer = $service->get('cus_123');
```

### Memory Management

DTOs are designed to be lightweight and garbage-collection friendly:

```php
// DTOs have no circular references
$customer = StripeCustomer::make(email: 'test@example.com');
unset($customer); // Immediately eligible for GC
```

### Collection Efficiency

Laravel Collections are used for list operations:

```php
// Efficient filtering without loading all data
$gmailCustomers = $service->list(['limit' => 100])
    ->filter(fn($c) => str_contains($c->email, '@gmail.com'))
    ->take(10);
```

## Security Considerations

### API Key Protection

API keys are never stored in DTOs or logged:

```php
// Keys are only in configuration
class Authentication
{
    public ?string $secretKey = null; // Not logged or serialized
}

// Services protect keys
public function toArray(): array
{
    // Never include API keys in array conversion
    return Arr::whereNotNull([
        'email' => $this->email,
        // 'api_key' is never included
    ]);
}
```

### Input Sanitization

All user input goes through Stripe's validation:

```php
// User input is validated by Stripe SDK
$customer = $service->create(StripeCustomer::make(
    email: $userInput['email'] // Stripe validates email format
));
```

### Test Isolation

Tests never use real API keys:

```php
// Test fake system prevents accidental real API calls
$fake = Stripe::fake([...]);
// No risk of real API calls with fake client
```

### Environment Separation

Clear separation between test and production:

```php
// Different environment variables
STRIPE_SECRET_KEY=sk_test_... # Test
STRIPE_SECRET_KEY=sk_live_... # Production

// Library automatically handles the difference
```

## Error Handling Strategy

### Exception Transparency

The library doesn't wrap Stripe exceptions, maintaining full error context:

```php
try {
    $customer = $service->create($customerData);
} catch (\Stripe\Exception\CardException $e) {
    // Direct access to Stripe's exception system
    $errorCode = $e->getStripeCode();
    $errorMessage = $e->getMessage();
}
```

### Validation at Boundaries

Validation occurs at the Stripe API boundary:

```php
// No validation in DTOs (Stripe handles it)
$customer = StripeCustomer::make(email: 'invalid-email');

// Validation happens here
$result = $service->create($customer); // Stripe throws exception for invalid email
```

### Graceful Degradation

Services handle missing data gracefully:

```php
public static function fromStripeObject(Customer $stripeCustomer): self
{
    return self::make(
        id: $stripeCustomer->id,
        email: $stripeCustomer->email ?? null, // Handle missing data
        name: $stripeCustomer->name ?? null
    );
}
```

## Evolution and Extensibility

### Backward Compatibility

The library maintains backward compatibility through:

1. **Immutable public APIs**: Service method signatures don't change
2. **Additive changes**: New fields are added as nullable properties
3. **Deprecation path**: Old methods are deprecated before removal

### Stripe SDK Compatibility

Support for multiple Stripe SDK versions:

```php
// Handles differences between SDK versions
$defaultPrice = null;
if (isset($stripeProduct->default_price)) {
    // SDK v16+ returns string or object
    $defaultPrice = is_string($stripeProduct->default_price)
        ? $stripeProduct->default_price
        : $stripeProduct->default_price->id;
}
```

### Future-Proofing

The architecture accommodates future Stripe changes:

1. **Flexible DTOs**: Easy to add new fields
2. **Extensible services**: New methods can be added
3. **Modular enums**: New constants can be added
4. **Testable design**: Changes can be tested thoroughly

This architecture ensures the library remains maintainable, extensible, and reliable as both Laravel and Stripe evolve.