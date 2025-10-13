# Stripe Facade API Reference

The `Stripe` class is the single entry point for all library operations. It provides factory methods for creating data objects and accessor methods for retrieving service
instances.

## Factory Methods

Factory methods create data objects without making API calls. These objects are then passed to service methods.

### Stripe::customer()

Creates a customer data object.

```php
public static function customer(mixed ...$params): StripeCustomer
```

**Parameters** (all optional, named parameters):

- `id` (string) - Stripe customer ID
- `email` (string) - Customer email address
- `name` (string) - Customer full name
- `phone` (string) - Customer phone number
- `description` (string) - Description of the customer
- `address` (StripeAddress) - Customer billing address
- `shipping` (StripeShipping) - Customer shipping information

**Returns**: `StripeCustomer` object

**Example**:

```php
$customer = Stripe::customer(
    email: 'customer@example.com',
    name: 'John Doe',
    phone: '+1-555-0100'
);
```

### Stripe::product()

Creates a product data object.

```php
public static function product(mixed ...$params): StripeProduct
```

**Parameters** (all optional, named parameters):

- `id` (string) - Stripe product ID
- `name` (string) - Product name
- `description` (string) - Product description
- `active` (bool) - Whether the product is available for purchase
- `images` (array) - List of image URLs
- `metadata` (array) - Key-value metadata
- `defaultPrice` (string) - Default price ID
- `taxCode` (string) - Tax code ID
- `unitLabel` (string) - Unit label (e.g., "per seat")
- `url` (string) - Product URL
- `shippable` (bool) - Whether the product can be shipped
- `packageDimensions` (array) - Package dimensions

**Returns**: `StripeProduct` object

**Example**:

```php
$product = Stripe::product(
    name: 'Premium Membership',
    description: 'Access to all premium features',
    active: true
);
```

### Stripe::price()

Creates a price data object.

```php
public static function price(mixed ...$params): StripePrice
```

**Parameters** (all optional, named parameters):

- `id` (string) - Stripe price ID
- `product` (string) - Product ID this price belongs to
- `active` (bool) - Whether the price is active
- `currency` (string) - Three-letter ISO currency code
- `unitAmount` (int) - Amount in cents
- `unitAmountDecimal` (string) - Decimal amount (for precise pricing)
- `type` (PriceType) - One-time or recurring
- `billingScheme` (BillingScheme) - Per-unit or tiered
- `recurring` (array) - Recurring price configuration
- `nickname` (string) - Descriptive name
- `metadata` (array) - Key-value metadata
- `lookupKey` (string) - Lookup key for price
- `tiers` (array) - Tiered pricing configuration
- `tiersMode` (TiersMode) - Volume or graduated tiers
- `taxBehavior` (TaxBehavior) - Tax behavior

**Returns**: `StripePrice` object

**Example**:

```php
$price = Stripe::price(
    product: 'prod_123',
    unitAmount: 2000,
    currency: 'usd',
    recurring: [
        'interval' => RecurringInterval::Month,
        'interval_count' => 1
    ]
);
```

### Stripe::subscription()

Creates a subscription data object.

```php
public static function subscription(mixed ...$params): StripeSubscription
```

**Parameters** (all optional, named parameters):

- `id` (string) - Stripe subscription ID
- `customer` (string) - Customer ID
- `status` (SubscriptionStatus) - Subscription status
- `items` (array) - Subscription items (prices and quantities)
- `defaultPaymentMethod` (string) - Default payment method ID
- `metadata` (array) - Key-value metadata
- `currency` (string) - Currency code
- `collectionMethod` (CollectionMethod) - Charge automatically or send invoice
- `cancelAtPeriodEnd` (bool) - Whether to cancel at period end
- `description` (string) - Description

**Returns**: `StripeSubscription` object

**Example**:

```php
$subscription = Stripe::subscription(
    customer: 'cus_123',
    items: [
        [
            'price' => 'price_123',
            'quantity' => 1
        ]
    ]
);
```

### Stripe::financialConnections()

Creates a financial connections data object.

```php
public static function financialConnections(mixed ...$params): StripeFinancialConnection
```

**Parameters**:

- `customer` (StripeCustomer) - Customer object
- `permissions` (array) - List of permissions (default: `['transactions']`)

**Returns**: `StripeFinancialConnection` object

**Example**:

```php
$connection = Stripe::financialConnections(
    customer: Stripe::customer(email: 'test@example.com'),
    permissions: ['transactions', 'balances']
);
```

### Stripe::webhook()

Creates a webhook data object.

```php
public static function webhook(mixed ...$params): StripeWebhook
```

**Parameters**:

- `url` (string) - Webhook endpoint URL
- `events` (array) - List of events to subscribe to (default: `[]`)

**Returns**: `StripeWebhook` object

**Example**:

```php
$webhook = Stripe::webhook(
    url: 'https://example.com/webhooks/stripe',
    events: ['customer.created', 'invoice.paid']
);
```

## Service Accessor Methods

Service accessor methods return service instances that make API calls.

### Stripe::customers()

Returns the customer service instance.

```php
public static function customers(): StripeCustomerService
```

**Returns**: `StripeCustomerService` instance

**Available Methods**:

- `create(StripeCustomer $customer): StripeCustomer`
- `get(string $customerId): StripeCustomer`
- `update(string $customerId, StripeCustomer $customer): StripeCustomer`
- `delete(string $customerId): bool`
- `list(array $params = []): Collection<StripeCustomer>`
- `search(string $query, array $params = []): Collection<StripeCustomer>`

**Example**:

```php
$service = Stripe::customers();
$customer = $service->create(Stripe::customer(email: 'test@example.com'));
```

### Stripe::products()

Returns the product service instance.

```php
public static function products(): StripeProductService
```

**Returns**: `StripeProductService` instance

**Available Methods**:

- `create(StripeProduct $product): StripeProduct`
- `get(string $productId): StripeProduct`
- `update(string $productId, StripeProduct $product): StripeProduct`
- `delete(string $productId): bool`
- `archive(string $productId): StripeProduct`
- `reactivate(string $productId): StripeProduct`
- `list(array $params = []): Collection<StripeProduct>`
- `search(string $query, array $params = []): Collection<StripeProduct>`

**Example**:

```php
$service = Stripe::products();
$product = $service->archive('prod_123');
```

### Stripe::prices()

Returns the price service instance.

```php
public static function prices(): StripePriceService
```

**Returns**: `StripePriceService` instance

**Available Methods**:

- `create(StripePrice $price): StripePrice`
- `get(string $priceId): StripePrice`
- `update(string $priceId, StripePrice $price): StripePrice`
- `archive(string $priceId): StripePrice`
- `reactivate(string $priceId): StripePrice`
- `list(array $params = []): Collection<StripePrice>`
- `listByProduct(string $productId, array $params = []): Collection<StripePrice>`
- `search(string $query, array $params = []): Collection<StripePrice>`
- `getByLookupKey(string $lookupKey): ?StripePrice`

**Example**:

```php
$service = Stripe::prices();
$prices = $service->listByProduct('prod_123');
```

### Stripe::subscriptions()

Returns the subscription service instance.

```php
public static function subscriptions(): StripeSubscriptionService
```

**Returns**: `StripeSubscriptionService` instance

**Available Methods**:

- `create(StripeSubscription $subscription): StripeSubscription`
- `get(string $subscriptionId): StripeSubscription`
- `update(string $subscriptionId, StripeSubscription $subscription): StripeSubscription`
- `cancel(string $subscriptionId): StripeSubscription`
- `cancelAtPeriodEnd(string $subscriptionId): StripeSubscription`
- `resume(string $subscriptionId): StripeSubscription`
- `list(array $params = []): Collection<StripeSubscription>`
- `search(string $query, array $params = []): Collection<StripeSubscription>`

**Example**:

```php
$service = Stripe::subscriptions();
$subscription = $service->cancelAtPeriodEnd('sub_123');
```

## Testing Method

### Stripe::fake()

Creates a fake Stripe client for testing.

```php
public static function fake(array $fakes = []): FakeStripeClient
```

**Parameters**:

- `fakes` (array) - Map of method names to responses

**Returns**: `FakeStripeClient` instance for assertions

**Example**:

```php
$fake = Stripe::fake([
    'customers.create' => StripeFixtures::customer(['id' => 'cus_test']),
]);

// Use services normally
$customer = Stripe::customers()->create(Stripe::customer(email: 'test@example.com'));

// Assert behavior
expect($fake)->toHaveCalledStripeMethod('customers.create');
```

See [Testing Guide](../testing.md) for comprehensive testing documentation.

## Usage Patterns

### Chaining Pattern

The most common pattern is to chain factory and service methods:

```php
$customer = Stripe::customers()->create(
    Stripe::customer(
        email: 'customer@example.com',
        name: 'John Doe'
    )
);
```

### Separate Steps Pattern

For complex operations, separate object creation from API calls:

```php
// Build the customer object
$customerData = Stripe::customer(
    email: 'customer@example.com',
    name: 'John Doe',
    phone: '+1-555-0100',
    address: Stripe::address(
        line1: '123 Main St',
        city: 'San Francisco',
        state: 'CA',
        postalCode: '94102',
        country: 'US'
    )
);

// Validate or modify if needed
if (shouldCreateCustomer()) {
    $customer = Stripe::customers()->create($customerData);
}
```

### Service Reuse Pattern

For multiple operations, store the service instance:

```php
$customerService = Stripe::customers();

$customer1 = $customerService->create(Stripe::customer(email: 'user1@example.com'));
$customer2 = $customerService->create(Stripe::customer(email: 'user2@example.com'));
$customer3 = $customerService->create(Stripe::customer(email: 'user3@example.com'));
```

## Error Handling

All methods that make API calls can throw `Stripe\Exception\ApiErrorException`:

```php
use Stripe\Exception\ApiErrorException;

try {
    $customer = Stripe::customers()->get('cus_invalid');
} catch (ApiErrorException $e) {
    logger()->error('Stripe API error', [
        'message' => $e->getMessage(),
        'type' => $e->getError()->type,
        'code' => $e->getError()->code,
    ]);
}
```

See [Error Handling Guide](../advanced/error-handling.md) for detailed information.

## Summary

The `Stripe` facade provides:

- **Factory methods** - Create data objects: `customer()`, `product()`, `price()`, `subscription()`
- **Service accessors** - Get service instances: `customers()`, `products()`, `prices()`, `subscriptions()`
- **Testing method** - Create fake client: `fake()`

All operations go through this single entry point, providing a clean and consistent API.
