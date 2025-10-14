# Customers

Customer management is the foundation of most applications. This chapter covers everything you need to know about working with Stripe customers in the Laravel
Stripe library—from basic CRUD operations to complex address handling and comprehensive testing strategies.

## Table of Contents

- [Basic Customer Operations](#basic-customer-operations)
- [Customer Data Objects](#customer-data-objects)
- [Address and Shipping](#address-and-shipping)
- [Advanced Querying](#advanced-querying)
- [Testing Customer Operations](#testing-customer-operations)
- [Common Patterns](#common-patterns)

## Basic Customer Operations

The customer service (accessed via `Stripe::customers()`) provides all the standard operations you need for customer management. All methods are fully typed and return consistent, predictable results.

### Creating Customers

```php
use EncoreDigitalGroup\Stripe\Stripe;

// Simple customer
$customer = Stripe::customers()->create(Stripe::customer(
    email: 'john@example.com',
    name: 'John Doe'
));

// Customer with description
$customer = Stripe::customers()->create(Stripe::customer(
    email: 'premium@example.com',
    name: 'Premium Customer',
    description: 'VIP customer with premium support',
    phone: '+1-555-123-4567'
));

echo $customer->id; // "cus_abc123..."
```

### Retrieving Customers

```php
// Get by ID
$customer = Stripe::customers()->get('cus_abc123');

// The response is fully typed
echo $customer->email; // IDE autocompletion works
echo $customer->name;
echo $customer->description;
```

### Updating Customers

```php
// Update with new data
$updatedCustomer = Stripe::customers()->update('cus_abc123', Stripe::customer(
    name: 'John Smith',  // Changed name
    phone: '+1-555-987-6543'  // Updated phone
    // email unchanged, so not included
));

// The response reflects the changes
echo $updatedCustomer->name; // "John Smith"
echo $updatedCustomer->phone; // "+1-555-987-6543"
echo $updatedCustomer->email; // Still the original email
```

### Deleting Customers

```php
$deleted = Stripe::customers()->delete('cus_abc123');

if ($deleted) {
    echo "Customer successfully deleted";
}
```

### Listing Customers

```php
// Get all customers (uses Laravel Collections)
$customers = $service->list();

// With pagination
$customers = $service->list([
    'limit' => 10,
    'starting_after' => 'cus_last_id'
]);

// Filter by creation date
$customers = $service->list([
    'created' => [
        'gte' => strtotime('2025-01-01')
    ]
]);

// Work with the collection
$customers->filter(fn($c) => str_contains($c->email, '@gmail.com'))
          ->map(fn($c) => $c->name)
          ->values()
          ->toArray();
```

## Customer Data Objects

The `StripeCustomer` class represents all customer data in a strongly-typed format. Understanding its structure is key to effective usage.

### StripeCustomer Properties

```php
use EncoreDigitalGroup\Stripe\Objects\Customer\StripeCustomer;
use EncoreDigitalGroup\Stripe\Objects\Support\StripeAddress;
use EncoreDigitalGroup\Stripe\Objects\Customer\StripeShipping;

$customer = StripeCustomer::make(
    id: 'cus_123',              // string|null - Stripe customer ID
    email: 'john@example.com',   // string|null - Email address
    name: 'John Doe',           // string|null - Full name
    description: 'VIP customer', // string|null - Custom description
    phone: '+1-555-123-4567',   // string|null - Phone number
    address: StripeAddress::make(/* ... */), // StripeAddress|null
    shipping: StripeShipping::make(/* ... */) // StripeShipping|null
);
```

### Object Conversion

The library automatically handles conversion between our DTOs and Stripe's SDK objects:

```php
// From Stripe SDK to our DTO
$stripeCustomer = $stripe->customers->retrieve('cus_123');
$ourCustomer = StripeCustomer::fromStripeObject($stripeCustomer);

// From our DTO to array for API calls
$customer = StripeCustomer::make(email: 'test@example.com');
$array = $customer->toArray();
// Returns: ['email' => 'test@example.com'] (nulls filtered out)
```

### Null Handling

The library intelligently handles null values. Only non-null fields are sent to Stripe:

```php
$customer = StripeCustomer::make(
    email: 'john@example.com',
    name: 'John Doe'
    // description, phone, address, shipping are null
);

$array = $customer->toArray();
// Result: ['email' => 'john@example.com', 'name' => 'John Doe']
// Null fields are automatically excluded
```

## Address and Shipping

Customers can have billing addresses and shipping information. The library provides dedicated objects for clean data management.

### Billing Address

```php
use EncoreDigitalGroup\Stripe\Objects\Support\StripeAddress;

$address = StripeAddress::make(
    line1: '123 Main Street',
    line2: 'Apt 4B',           // Optional
    city: 'San Francisco',
    state: 'CA',
    postalCode: '94105',
    country: 'US'
);

$customer = $service->create(StripeCustomer::make(
    email: 'customer@example.com',
    name: 'John Doe',
    address: $address
));
```

### Shipping Information

Shipping requires both an address and a name, and optionally includes a phone number:

```php
use EncoreDigitalGroup\Stripe\Objects\Customer\StripeShipping;

$shippingAddress = StripeAddress::make(
    line1: '456 Shipping Lane',
    city: 'Los Angeles',
    state: 'CA',
    postalCode: '90210',
    country: 'US'
);

$shipping = StripeShipping::make(
    address: $shippingAddress,
    name: 'John Doe',
    phone: '+1-555-123-4567'  // Optional
);

$customer = $service->create(StripeCustomer::make(
    email: 'customer@example.com',
    name: 'John Doe',
    shipping: $shipping
));
```

### Address Conversion

The library handles the differences between our camelCase properties and Stripe's snake_case API:

```php
// Our object
$address = StripeAddress::make(
    postalCode: '12345'  // camelCase
);

// Converts to API format
$array = $address->toArray();
// Result: ['postal_code' => '12345']  // snake_case
```

### Retrieving Address Data

When you retrieve a customer, addresses are automatically converted back to our objects:

```php
$customer = $service->get('cus_with_address');

if ($customer->address) {
    echo $customer->address->line1;
    echo $customer->address->city;
    echo $customer->address->postalCode; // Automatically converted to camelCase
}

if ($customer->shipping) {
    echo $customer->shipping->name;
    echo $customer->shipping->address->line1;
    echo $customer->shipping->phone;
}
```

## Advanced Querying

The customer service provides powerful querying capabilities through list and search operations.

### List Filtering

```php
// Recent customers
$recent = $service->list([
    'created' => [
        'gte' => strtotime('-30 days')
    ],
    'limit' => 50
]);

// Customers with email
$withEmail = $service->list([
    'email' => 'specific@example.com'
]);

// Pagination
$page1 = $service->list(['limit' => 10]);
$page2 = $service->list([
    'limit' => 10,
    'starting_after' => $page1->last()->id
]);
```

### Search Operations

Stripe's search API allows complex queries using a special syntax:

```php
// Email domain search
$gmailUsers = $service->search("email~'@gmail.com'");

// Name search
$johns = $service->search("name~'John'");

// Metadata search
$vipCustomers = $service->search("metadata['tier']:'vip'");

// Combined search
$recentVips = $service->search("metadata['tier']:'vip' AND created>1640995200");

// With additional parameters
$results = $service->search("email~'@company.com'", [
    'limit' => 20
]);
```

### Working with Search Results

Both list and search return Laravel Collections with full type safety:

```php
$customers = $service->search("email~'@example.com'");

// Filter and transform
$customerData = $customers
    ->filter(fn($customer) => $customer->name !== null)
    ->map(fn($customer) => [
        'id' => $customer->id,
        'name' => $customer->name,
        'email' => $customer->email,
        'has_address' => $customer->address !== null
    ])
    ->values();

// Group by domain
$byDomain = $customers
    ->groupBy(fn($customer) => explode('@', $customer->email)[1])
    ->map(fn($group) => $group->count());
```

## Testing Customer Operations

The library provides testing utilities that make it easy to test customer operations without making real API calls.

### Basic Testing Setup

```php
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeFixtures;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeMethod;

test('can create a customer', function () {
    // Arrange: Set up the fake response
    $fake = Stripe::fake([
        StripeMethod::CustomersCreate->value => StripeFixtures::customer([
            'id' => 'cus_test123',
            'email' => 'test@example.com',
            'name' => 'Test Customer'
        ])
    ]);

    // Act: Create the customer
    $service = StripeCustomerService::make();
    $customer = $service->create(StripeCustomer::make(
        email: 'test@example.com',
        name: 'Test Customer'
    ));

    // Assert: Verify the response and API call
    expect($customer)
        ->toBeInstanceOf(StripeCustomer::class)
        ->and($customer->id)->toBe('cus_test123')
        ->and($customer->email)->toBe('test@example.com')
        ->and($fake)->toHaveCalledStripeMethod(StripeMethod::CustomersCreate);
});
```

### Dynamic Testing with Callables

```php
test('creates customer with dynamic response', function () {
    Stripe::fake([
        'customers.create' => function (array $params) {
            return StripeFixtures::customer([
                'id' => 'cus_dynamic',
                'email' => $params['email'] ?? 'default@example.com',
                'name' => $params['name'] ?? 'Default Name'
            ]);
        }
    ]);

    $service = StripeCustomerService::make();
    $customer = $service->create(StripeCustomer::make(
        email: 'dynamic@example.com',
        name: 'Dynamic Name'
    ));

    expect($customer->email)->toBe('dynamic@example.com')
        ->and($customer->name)->toBe('Dynamic Name');
});
```

### Testing List Operations

```php
test('can list customers', function () {
    $fake = Stripe::fake([
        'customers.all' => StripeFixtures::customerList([
            StripeFixtures::customer(['id' => 'cus_1', 'email' => 'user1@example.com']),
            StripeFixtures::customer(['id' => 'cus_2', 'email' => 'user2@example.com']),
            StripeFixtures::customer(['id' => 'cus_3', 'email' => 'user3@example.com'])
        ])
    ]);

    $service = StripeCustomerService::make();
    $customers = $service->list(['limit' => 10]);

    expect($customers)
        ->toHaveCount(3)
        ->and($customers->first())->toBeInstanceOf(StripeCustomer::class)
        ->and($customers->first()->id)->toBe('cus_1')
        ->and($fake)->toHaveCalledStripeMethod('customers.all');
});
```

### Testing with Address Data

```php
test('creates customer with address', function () {
    $fake = Stripe::fake([
        'customers.create' => StripeFixtures::customer([
            'id' => 'cus_with_address',
            'email' => 'test@example.com',
            'address' => [
                'line1' => '123 Test Street',
                'city' => 'Test City',
                'state' => 'TC',
                'postal_code' => '12345',
                'country' => 'US'
            ]
        ])
    ]);

    $service = StripeCustomerService::make();
    $customer = $service->create(StripeCustomer::make(
        email: 'test@example.com',
        address: StripeAddress::make(
            line1: '123 Test Street',
            city: 'Test City',
            state: 'TC',
            postalCode: '12345',
            country: 'US'
        )
    ));

    expect($customer->address)->not->toBeNull()
        ->and($customer->address->line1)->toBe('123 Test Street')
        ->and($customer->address->postalCode)->toBe('12345');
});
```

### Advanced Test Assertions

```php
test('tracks multiple API calls', function () {
    $fake = Stripe::fake([
        'customers.create' => StripeFixtures::customer(),
        'customers.update' => StripeFixtures::customer(['name' => 'Updated']),
        'customers.delete' => StripeFixtures::deleted('cus_123')
    ]);

    $service = StripeCustomerService::make();

    // Make multiple calls
    $customer = $service->create(StripeCustomer::make(email: 'test@example.com'));
    $service->update($customer->id, StripeCustomer::make(name: 'Updated'));
    $service->delete($customer->id);

    // Verify all calls were made
    expect($fake)
        ->toHaveCalledStripeMethodTimes('customers.create', 1)
        ->toHaveCalledStripeMethodTimes('customers.update', 1)
        ->toHaveCalledStripeMethodTimes('customers.delete', 1)
        ->toNotHaveCalledStripeMethod('customers.retrieve');
});
```

## Common Patterns

Here are some common patterns you'll use when working with customers in real applications.

### Customer Registration Flow

```php
class CustomerRegistrationService
{
    public function __construct(
        private StripeCustomerService $stripeCustomerService
    ) {}

    public function registerCustomer(User $user, ?array $addressData = null): StripeCustomer
    {
        $address = null;
        if ($addressData) {
            $address = StripeAddress::make(
                line1: $addressData['line1'],
                line2: $addressData['line2'] ?? null,
                city: $addressData['city'],
                state: $addressData['state'],
                postalCode: $addressData['postal_code'],
                country: $addressData['country'] ?? 'US'
            );
        }

        $customer = $this->stripeCustomerService->create(StripeCustomer::make(
            email: $user->email,
            name: $user->name,
            description: "User ID: {$user->id}",
            address: $address
        ));

        // Store the Stripe customer ID
        $user->update(['stripe_customer_id' => $customer->id]);

        return $customer;
    }
}
```

### Customer Profile Updates

```php
class CustomerProfileService
{
    public function updateProfile(User $user, array $profileData): StripeCustomer
    {
        $updateData = StripeCustomer::make(
            name: $profileData['name'] ?? null,
            phone: $profileData['phone'] ?? null
        );

        // Update address if provided
        if (isset($profileData['address'])) {
            $updateData = StripeCustomer::make(
                name: $profileData['name'] ?? null,
                phone: $profileData['phone'] ?? null,
                address: StripeAddress::make(
                    line1: $profileData['address']['line1'],
                    line2: $profileData['address']['line2'] ?? null,
                    city: $profileData['address']['city'],
                    state: $profileData['address']['state'],
                    postalCode: $profileData['address']['postal_code'],
                    country: $profileData['address']['country'] ?? 'US'
                )
            );
        }

        $service = StripeCustomerService::make();
        return $service->update($user->stripe_customer_id, $updateData);
    }
}
```

### Customer Search and Analytics

```php
class CustomerAnalyticsService
{
    public function getCustomersByDomain(string $domain): Collection
    {
        $service = StripeCustomerService::make();
        return $service->search("email~'@{$domain}'");
    }

    public function getRecentCustomers(int $days = 30): Collection
    {
        $service = StripeCustomerService::make();

        return $service->list([
            'created' => [
                'gte' => strtotime("-{$days} days")
            ],
            'limit' => 100
        ]);
    }

    public function getCustomerStats(): array
    {
        $service = StripeCustomerService::make();
        $allCustomers = $service->list(['limit' => 100]);

        return [
            'total' => $allCustomers->count(),
            'with_addresses' => $allCustomers->filter(fn($c) => $c->address !== null)->count(),
            'with_phone' => $allCustomers->filter(fn($c) => $c->phone !== null)->count(),
            'domains' => $allCustomers
                ->map(fn($c) => explode('@', $c->email)[1])
                ->countBy()
                ->sortDesc()
                ->take(10)
                ->toArray()
        ];
    }
}
```

### Error Handling

```php
use Stripe\Exception\ApiErrorException;

class SafeCustomerService
{
    public function createCustomerSafely(array $customerData): ?StripeCustomer
    {
        try {
            $service = StripeCustomerService::make();

            return $service->create(StripeCustomer::make(
                email: $customerData['email'],
                name: $customerData['name'],
                phone: $customerData['phone'] ?? null
            ));

        } catch (ApiErrorException $e) {
            // Log the error
            logger()->error('Stripe customer creation failed', [
                'error' => $e->getMessage(),
                'stripe_error_code' => $e->getStripeCode(),
                'customer_data' => $customerData
            ]);

            // Return null or throw a custom exception
            return null;
        }
    }
}
```

## Next Steps

Now that you understand customer management, you're ready to explore products and pricing:

- **[Products](03-products.md)** - Learn about product creation, management, and lifecycle
- **[Prices](04-prices.md)** - Master complex pricing including recurring billing and tiers

Or dive deeper into the testing infrastructure:

- **[Testing](05-testing.md)** - Comprehensive guide to testing Stripe integrations