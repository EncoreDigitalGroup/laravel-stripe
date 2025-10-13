# Stripe::customer()

Creates a `StripeCustomer` data transfer object (DTO) for representing customer information. This is a factory method that does not make any API calls - it simply creates
a typed object that can be passed to service methods.

## Signature

```php
public static function customer(mixed ...$params): StripeCustomer
```

## Parameters

All parameters are optional and use named argument syntax:

| Parameter     | Type              | Description                          |
|---------------|-------------------|--------------------------------------|
| `id`          | `?string`         | Stripe customer ID (e.g., `cus_xxx`) |
| `email`       | `?string`         | Customer's email address             |
| `name`        | `?string`         | Customer's full name                 |
| `phone`       | `?string`         | Customer's phone number              |
| `description` | `?string`         | Internal description of the customer |
| `address`     | `?StripeAddress`  | Customer's billing address           |
| `shipping`    | `?StripeShipping` | Customer's shipping information      |

## Returns

`StripeCustomer` - An immutable data transfer object representing a Stripe customer

## Usage Examples

### Basic Customer

```php
use EncoreDigitalGroup\Stripe\Stripe;

$customer = Stripe::customer(
    email: 'john.doe@example.com',
    name: 'John Doe'
);
```

### Customer with Phone

```php
$customer = Stripe::customer(
    email: 'jane.smith@example.com',
    name: 'Jane Smith',
    phone: '+1-555-0123'
);
```

### Customer with Address

```php
use EncoreDigitalGroup\Stripe\Objects\Support\StripeAddress;

$customer = Stripe::customer(
    email: 'customer@example.com',
    name: 'John Doe',
    address: StripeAddress::make(
        line1: '123 Main Street',
        line2: 'Apt 4B',
        city: 'San Francisco',
        state: 'CA',
        postalCode: '94102',
        country: 'US'
    )
);
```

### Customer with Shipping Information

```php
use EncoreDigitalGroup\Stripe\Objects\Support\StripeAddress;
use EncoreDigitalGroup\Stripe\Objects\Customer\StripeShipping;

$shippingAddress = StripeAddress::make(
    line1: '456 Oak Avenue',
    city: 'Portland',
    state: 'OR',
    postalCode: '97204',
    country: 'US'
);

$customer = Stripe::customer(
    email: 'customer@example.com',
    name: 'John Doe',
    shipping: StripeShipping::make(
        address: $shippingAddress,
        name: 'John Doe',
        phone: '+1-555-0123'
    )
);
```

### Complete Customer Object

```php
$customer = Stripe::customer(
    email: 'premium@example.com',
    name: 'Premium Customer',
    phone: '+1-555-9999',
    description: 'VIP customer - premium tier',
    address: StripeAddress::make(
        line1: '789 Business Blvd',
        city: 'New York',
        state: 'NY',
        postalCode: '10001',
        country: 'US'
    )
);
```

## Using with Services

The `StripeCustomer` object is typically passed to service methods:

### Creating a Customer

```php
// Create the customer object
$customerData = Stripe::customer(
    email: 'new.customer@example.com',
    name: 'New Customer'
);

// Send to Stripe API
$createdCustomer = Stripe::customers()->create($customerData);

echo "Created customer: {$createdCustomer->id}";
```

### Updating a Customer

```php
// Build update data
$updateData = Stripe::customer(
    name: 'Updated Name',
    phone: '+1-555-0000'
);

// Update via API
$updatedCustomer = Stripe::customers()->update('cus_xxx', $updateData);
```

### Chained Pattern

```php
// Most common: chain factory and service call
$customer = Stripe::customers()->create(
    Stripe::customer(
        email: 'quick@example.com',
        name: 'Quick Customer'
    )
);
```

## Object Properties

The `StripeCustomer` object exposes all parameters as public readonly properties:

```php
$customer = Stripe::customer(
    email: 'test@example.com',
    name: 'Test User'
);

echo $customer->email; // "test@example.com"
echo $customer->name;  // "Test User"
echo $customer->id;    // null (not set)
```

## Data Conversion

### To Array

Convert the customer object to an array for API requests:

```php
$customer = Stripe::customer(
    email: 'test@example.com',
    name: 'Test User'
);

$array = $customer->toArray();
// [
//     "email" => "test@example.com",
//     "name" => "Test User"
// ]
// Note: null values are filtered out
```

### From Stripe Object

Convert a Stripe SDK customer object to a typed DTO:

```php
use Stripe\Customer as StripeCustomerSDK;

// After receiving from Stripe API
$sdkCustomer = $stripeClient->customers->retrieve('cus_xxx');

// Convert to our DTO
$customer = StripeCustomer::fromStripeObject($sdkCustomer);
```

## Type Safety

The `StripeCustomer` object provides compile-time type safety:

```php
// IDE autocomplete and type checking
$customer = Stripe::customer(
    email: 'test@example.com',
    // IDE will suggest: name, phone, description, address, shipping
);

// Type errors caught at static analysis
$customer = Stripe::customer(
    email: 123 // ❌ PHPStan error: expected string, got int
);
```

## Validation

Email validation should be done at the application layer before creating the customer:

```php
$email = request()->input('email');

// Validate first
$validated = validator(['email' => $email], [
    'email' => 'required|email|max:255'
])->validate();

// Then create customer
$customer = Stripe::customer(
    email: $validated['email']
);
```

## Related Objects

- **StripeAddress** - Used for `address` parameter: [stripe-address.md](stripe-address.md)
- **StripeShipping** - Used for `shipping` parameter: [stripe-shipping.md](stripe-shipping.md)

## Related Services

- **StripeCustomerService** - CRUD operations: [stripe-customers-service.md](stripe-customers-service.md)

## See Also

- [Customer Service Documentation](stripe-customers-service.md)
- [Testing with Customers](../testing.md#customer-testing)
- [Quick Start Guide](../quick-start.md)
- [Stripe Customer API Reference](https://stripe.com/docs/api/customers)