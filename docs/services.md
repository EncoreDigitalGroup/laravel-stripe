# Service Pattern Guide

The service layer handles all communication with Stripe's API. This guide explains how services work and why they're designed the way they are.

## Service Responsibilities

Each service is responsible for:

1. **API Communication** - Making requests to Stripe
2. **Data Transformation** - Converting between DTOs and API formats
3. **Field Filtering** - Removing read-only and immutable fields
4. **Error Propagation** - Letting Stripe exceptions bubble up
5. **Response Mapping** - Converting API responses to DTOs

## Accessing Services

All services are accessed through the `Stripe` facade:

```php
use EncoreDigitalGroup\Stripe\Stripe;

$customerService = Stripe::customers();
$productService = Stripe::products();
$priceService = Stripe::prices();
$subscriptionService = Stripe::subscriptions();
```

**Why through the facade**: The facade handles service initialization, configuration, and dependency injection. You don't need to worry about constructors or
configuration.

## Common Service Patterns

All services follow consistent patterns for CRUD operations.

### Create Pattern

```php
// 1. Create a data object
$customerData = Stripe::customer(
    email: 'customer@example.com',
    name: 'Jane Smith'
);

// 2. Pass to service's create method
$customer = Stripe::customers()->create($customerData);

// 3. Returns a DTO with the Stripe ID
echo $customer->id; // cus_...
```

**What happens internally**:

1. DTO is converted to array via `toArray()`
2. Null values are filtered out
3. Read-only fields (id, created, etc.) are removed
4. Array is sent to Stripe API
5. Response is converted back to DTO

### Retrieve Pattern

```php
// Pass the Stripe ID
$customer = Stripe::customers()->get('cus_...');

// Returns fully populated DTO
echo $customer->email;
echo $customer->name;
```

**What happens internally**:

1. API call to retrieve object
2. Response is converted to DTO via `fromStripeObject()`
3. All fields are populated from API response

### Update Pattern

```php
// Create object with ONLY fields to update
$updates = Stripe::customer(
    name: 'Jane Doe' // Only updating the name
);

// Pass ID and update object
$customer = Stripe::customers()->update('cus_...', $updates);
```

**What happens internally**:

1. DTO is converted to array
2. Null values are filtered (this is why partial updates work)
3. Immutable fields are removed (varies by resource)
4. Update request sent to Stripe
5. Updated object returned as DTO

### Delete Pattern

```php
// Some resources support deletion
$deleted = Stripe::products()->delete('prod_...');

// Returns boolean
if ($deleted) {
    echo "Product deleted successfully";
}
```

### List Pattern

```php
// List without parameters
$customers = Stripe::customers()->list();

// List with parameters
$customers = Stripe::customers()->list([
    'limit' => 10,
    'starting_after' => 'cus_...'
]);

// Returns Laravel Collection
foreach ($customers as $customer) {
    echo $customer->email;
}
```

**What happens internally**:

1. API call with parameters
2. Response data array is mapped
3. Each item is converted to DTO
4. Returns Laravel Collection of DTOs

## Resource-Specific Patterns

### Archive/Reactivate Pattern

Some resources can be archived (soft deleted) instead of deleted:

```php
// Archive a product (sets active = false)
$product = Stripe::products()->archive('prod_...');

// Reactivate an archived product
$product = Stripe::products()->reactivate('prod_...');

// Prices can only be archived, not deleted
$price = Stripe::prices()->archive('price_...');
$price = Stripe::prices()->reactivate('price_...');
```

**Why archive instead of delete**: Stripe maintains historical data for reporting and auditing. Archived resources remain accessible but don't appear in active lists.

### Subscription Lifecycle

Subscriptions have special lifecycle management:

```php
// Cancel immediately
$subscription = Stripe::subscriptions()->cancel('sub_...');

// Cancel at end of billing period
$subscription = Stripe::subscriptions()->cancelAtPeriodEnd('sub_...');

// Resume a subscription marked for cancellation
$subscription = Stripe::subscriptions()->resume('sub_...');
```

**Why special methods**: These operations require specific Stripe API calls and have business logic implications. Dedicated methods make the intent clear.

### Price Lookup Key

Prices support lookup by a custom key:

```php
// Find price by lookup key
$price = Stripe::prices()->getByLookupKey('premium_monthly');

// Returns null if not found
if ($price === null) {
    echo "Price not found";
}
```

**Why lookup keys**: Instead of hardcoding price IDs, you can use meaningful keys. This makes code more maintainable and environment-portable.

### Product-Specific Price Listing

List all prices for a specific product:

```php
$prices = Stripe::prices()->listByProduct('prod_...');

// Automatically filters by product
foreach ($prices as $price) {
    echo $price->unitAmount;
}
```

## Immutable Field Handling

Different resources have different immutable fields. Services automatically remove these before updates.

### Price Immutable Fields

After creation, prices can only update these fields:

- `active`
- `metadata`
- `nickname`
- `lookup_key`
- `tax_behavior`

All other fields (amount, currency, recurring, etc.) are immutable.

```php
$updates = Stripe::price(
    nickname: 'Premium Plan V2', // OK
    active: false,                // OK
    unitAmount: 2000             // Ignored (immutable)
);

$price = Stripe::prices()->update('price_...', $updates);
```

**Why automatic removal**: If you send immutable fields, Stripe returns an error. The service protects you by removing them automatically.

### Product Fields

Products allow updating most fields:

```php
$updates = Stripe::product(
    name: 'Updated Name',        // OK
    description: 'New description', // OK
    active: false,               // OK
    images: ['https://...']      // OK
);

$product = Stripe::products()->update('prod_...', $updates);
```

Only `id`, `created`, and `updated` are read-only.

## Search Operations

Some resources support Stripe's search API:

```php
// Search customers by email
$customers = Stripe::customers()->search(
    query: 'email:"customer@example.com"'
);

// Search products by name
$products = Stripe::products()->search(
    query: 'name:"premium"'
);

// Search with additional parameters
$results = Stripe::products()->search(
    query: 'active:"true"',
    params: ['limit' => 5]
);
```

**Search query syntax**: Uses Stripe's search query language. See [Stripe's search documentation](https://stripe.com/docs/search) for syntax details.

## Service Configuration

### Automatic Configuration

Services automatically configure themselves from the environment:

```php
// Reads STRIPE_SECRET_KEY from .env
$service = Stripe::customers();

// Ready to use immediately
$customer = $service->get('cus_...');
```

### Container Integration

In Laravel, services are container-aware:

```php
// The facade resolves from the container
$service1 = Stripe::customers();

// Same instance every time (singleton)
$service2 = Stripe::customers();

// $service1 === $service2
```

### Testing Override

Tests can inject fake clients:

```php
// Bind fake client to container
Stripe::fake([
    'customers.create' => $fakeResponse
]);

// Services automatically use the fake
$service = Stripe::customers();
```

See [Testing Guide](testing.md) for details.

## Error Handling

Services don't catch Stripe exceptions. Errors bubble up to your code:

```php
use Stripe\Exception\ApiErrorException;

try {
    $customer = Stripe::customers()->create($data);
} catch (ApiErrorException $e) {
    // Handle specific error
    if ($e->getError()->code === 'resource_missing') {
        // Customer not found
    }
}
```

**Why no error catching**: Your application knows best how to handle errors. The service layer stays simple and predictable.

## Performance Considerations

### Lazy Initialization

Services are only initialized when first accessed:

```php
// No API call, no initialization
$service = Stripe::customers();

// Initialization happens here
$customer = $service->get('cus_...');
```

### Batch Operations

For bulk operations, use list/search instead of multiple gets:

```php
// Inefficient
foreach ($customerIds as $id) {
    $customer = Stripe::customers()->get($id);
    // process
}

// Better
$customers = Stripe::customers()->list(['limit' => 100]);
foreach ($customers as $customer) {
    // process
}
```

## Summary

The service pattern provides:

- **Consistency** - Same patterns across all resources
- **Simplicity** - No boilerplate in your code
- **Safety** - Automatic field filtering
- **Integration** - Laravel container awareness
- **Testability** - Easy to fake for tests

Services handle the complexity of API communication so you can focus on business logic.
