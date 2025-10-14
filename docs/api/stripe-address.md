# Stripe::address()

Creates a `StripeAddress` data transfer object (DTO) for representing physical addresses. This is a factory method that does not make any API calls - it simply creates a
typed object that can be used with customer billing addresses, shipping addresses, and other address fields in the Stripe API.

## Signature

```php
public static function address(mixed ...$params): StripeAddress
```

## Parameters

All parameters are optional and use named argument syntax:

| Parameter    | Type      | Description                                                    |
|--------------|-----------|----------------------------------------------------------------|
| `line1`      | `?string` | Address line 1 (street address, P.O. Box, company name)        |
| `line2`      | `?string` | Address line 2 (apartment, suite, unit, building, floor, etc.) |
| `city`       | `?string` | City, district, suburb, town, or village                       |
| `state`      | `?string` | State, county, province, or region                             |
| `postalCode` | `?string` | ZIP or postal code                                             |
| `country`    | `?string` | Two-letter country code (ISO 3166-1 alpha-2)                   |

## Returns

`StripeAddress` - An immutable data transfer object representing an address

## Usage Examples

### Basic Address

```php
use EncoreDigitalGroup\Stripe\Stripe;

$address = Stripe::address(
    line1: '123 Main Street',
    city: 'San Francisco',
    state: 'CA',
    postalCode: '94102',
    country: 'US'
);
```

### Address with Suite/Apartment

```php
$address = Stripe::address(
    line1: '456 Market Street',
    line2: 'Suite 500',
    city: 'San Francisco',
    state: 'CA',
    postalCode: '94103',
    country: 'US'
);
```

### International Address

```php
$address = Stripe::address(
    line1: '10 Downing Street',
    city: 'London',
    postalCode: 'SW1A 2AA',
    country: 'GB'
);
```

### Minimal Address

```php
$address = Stripe::address(
    line1: '789 Pine Ave',
    city: 'Portland',
    postalCode: '97204'
);
```

## Using with Customer Objects

Addresses are commonly used with customer billing and shipping information:

### Customer with Billing Address

```php
$customer = Stripe::customer(
    email: 'customer@example.com',
    name: 'John Doe',
    address: Stripe::address(
        line1: '123 Main St',
        city: 'New York',
        state: 'NY',
        postalCode: '10001',
        country: 'US'
    )
);

$createdCustomer = Stripe::customers()->create($customer);
```

### Customer with Shipping Address

```php
use EncoreDigitalGroup\Stripe\Objects\Customer\StripeShipping;

$shippingAddress = Stripe::address(
    line1: '456 Oak Ave',
    line2: 'Apt 4B',
    city: 'Portland',
    state: 'OR',
    postalCode: '97204',
    country: 'US'
);

$customer = Stripe::customer(
    email: 'customer@example.com',
    name: 'Jane Smith',
    shipping: StripeShipping::make(
        address: $shippingAddress,
        name: 'Jane Smith',
        phone: '+1-555-0123'
    )
);
```

### Separate Billing and Shipping

```php
$billingAddress = Stripe::address(
    line1: '100 Business Blvd',
    city: 'Chicago',
    state: 'IL',
    postalCode: '60601',
    country: 'US'
);

$shippingAddress = Stripe::address(
    line1: '200 Home Street',
    city: 'Chicago',
    state: 'IL',
    postalCode: '60602',
    country: 'US'
);

$customer = Stripe::customer(
    email: 'customer@example.com',
    name: 'Business User',
    address: $billingAddress,
    shipping: StripeShipping::make(
        address: $shippingAddress,
        name: 'Business User'
    )
);
```

## Object Properties

The `StripeAddress` object exposes all parameters as public readonly properties:

```php
$address = Stripe::address(
    line1: '123 Main St',
    city: 'Seattle',
    state: 'WA',
    postalCode: '98101'
);

echo $address->line1;       // "123 Main St"
echo $address->city;        // "Seattle"
echo $address->state;       // "WA"
echo $address->postalCode;  // "98101"
echo $address->line2;       // null (not set)
```

## Data Conversion

### To Array

Convert the address object to an array for API requests:

```php
$address = Stripe::address(
    line1: '123 Main St',
    city: 'Seattle',
    state: 'WA',
    postalCode: '98101',
    country: 'US'
);

$array = $address->toArray();
// [
//     "line1" => "123 Main St",
//     "city" => "Seattle",
//     "state" => "WA",
//     "postal_code" => "98101",  // Note: snake_case conversion
//     "country" => "US"
// ]
```

Note: The `toArray()` method automatically converts `postalCode` to `postal_code` to match Stripe's API naming convention.

## Country Codes

The `country` parameter expects two-letter ISO 3166-1 alpha-2 country codes:

```php
// United States
$address = Stripe::address(country: 'US');

// United Kingdom
$address = Stripe::address(country: 'GB');

// Canada
$address = Stripe::address(country: 'CA');

// Germany
$address = Stripe::address(country: 'DE');

// France
$address = Stripe::address(country: 'FR');

// Australia
$address = Stripe::address(country: 'AU');

// Japan
$address = Stripe::address(country: 'JP');
```

[Full list of ISO 3166-1 alpha-2 codes](https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2)

## Common Patterns

### Form Input to Address

```php
// Laravel request example
$address = Stripe::address(
    line1: $request->input('address_line1'),
    line2: $request->input('address_line2'),
    city: $request->input('city'),
    state: $request->input('state'),
    postalCode: $request->input('postal_code'),
    country: $request->input('country', 'US')
);
```

### Updating Customer Address

```php
// Get existing customer
$customer = Stripe::customers()->get('cus_xxx');

// Create new address
$newAddress = Stripe::address(
    line1: '999 New Street',
    city: 'Boston',
    state: 'MA',
    postalCode: '02101',
    country: 'US'
);

// Update customer with new address
$updatedCustomer = Stripe::customers()->update(
    $customer->id,
    Stripe::customer(address: $newAddress)
);
```

### Address Validation

```php
// Validate before creating address
$validated = $request->validate([
    'line1' => 'required|string|max:255',
    'line2' => 'nullable|string|max:255',
    'city' => 'required|string|max:100',
    'state' => 'nullable|string|max:100',
    'postal_code' => 'required|string|max:20',
    'country' => 'required|string|size:2'
]);

$address = Stripe::address(
    line1: $validated['line1'],
    line2: $validated['line2'] ?? null,
    city: $validated['city'],
    state: $validated['state'] ?? null,
    postalCode: $validated['postal_code'],
    country: $validated['country']
);
```

### Conditional Address

```php
// Only include address if provided
$customer = Stripe::customer(
    email: 'customer@example.com',
    name: 'Customer Name',
    address: $hasAddress ? Stripe::address(
        line1: $addressData['line1'],
        city: $addressData['city'],
        postalCode: $addressData['postal_code']
    ) : null
);
```

## Regional Variations

### US Address

```php
$usAddress = Stripe::address(
    line1: '1600 Pennsylvania Avenue NW',
    city: 'Washington',
    state: 'DC',
    postalCode: '20500',
    country: 'US'
);
```

### UK Address

```php
$ukAddress = Stripe::address(
    line1: '10 Downing Street',
    city: 'London',
    postalCode: 'SW1A 2AA',
    country: 'GB'
);
// Note: UK addresses may not have a state
```

### Canadian Address

```php
$canadianAddress = Stripe::address(
    line1: '24 Sussex Drive',
    city: 'Ottawa',
    state: 'ON',
    postalCode: 'K1M 1M4',
    country: 'CA'
);
```

### Australian Address

```php
$australianAddress = Stripe::address(
    line1: '1 Sydney Harbour Bridge',
    city: 'Sydney',
    state: 'NSW',
    postalCode: '2000',
    country: 'AU'
);
```

## Type Safety

The `StripeAddress` object provides compile-time type safety:

```php
// IDE autocomplete and type checking
$address = Stripe::address(
    line1: '123 Main St',
    // IDE will suggest: line2, city, state, postalCode, country
);

// Type errors caught at static analysis
$address = Stripe::address(
    line1: 123 // ❌ PHPStan error: expected string, got int
);
```

## Immutability

Address objects are immutable. To modify an address, create a new one:

```php
$originalAddress = Stripe::address(
    line1: '123 Old St',
    city: 'Seattle'
);

// Create new address with updated values
$newAddress = Stripe::address(
    line1: '456 New St',
    city: 'Seattle'
);
```

## Testing

### Using in Tests

```php
test('creates customer with address', function () {
    $fake = Stripe::fake([
        'customers.create' => StripeFixtures::customer([
            'id' => 'cus_test',
            'email' => 'test@example.com'
        ])
    ]);

    $address = Stripe::address(
        line1: '123 Test St',
        city: 'Test City',
        postalCode: '12345'
    );

    $customer = Stripe::customers()->create(
        Stripe::customer(
            email: 'test@example.com',
            address: $address
        )
    );

    expect($customer->id)->toBe('cus_test')
        ->and($fake)->toHaveCalledStripeMethod('customers.create');
});
```

### Factory Pattern for Testing

```php
// Test helper
function createTestAddress(array $overrides = []): StripeAddress
{
    return Stripe::address(
        line1: $overrides['line1'] ?? '123 Test Street',
        line2: $overrides['line2'] ?? null,
        city: $overrides['city'] ?? 'Test City',
        state: $overrides['state'] ?? 'TS',
        postalCode: $overrides['postal_code'] ?? '12345',
        country: $overrides['country'] ?? 'US'
    );
}

// Use in tests
$address = createTestAddress(['city' => 'Seattle']);
```

## Validation Considerations

### Required vs Optional Fields

Stripe's requirements vary by payment method and country:

- Some payment methods require complete addresses
- Some countries don't use state/province
- Postal code formats vary by country

**Example: Minimal valid address**

```php
// This may be sufficient for some cases
$address = Stripe::address(
    line1: '123 Main St',
    postalCode: '12345'
);
```

**Example: Complete address**

```php
// Recommended for maximum compatibility
$address = Stripe::address(
    line1: '123 Main St',
    city: 'San Francisco',
    state: 'CA',
    postalCode: '94102',
    country: 'US'
);
```

## Related Objects

- **StripeCustomer** - Uses addresses for billing: [stripe-customer.md](stripe-customer.md)
- **StripeShipping** - Uses addresses for shipping: [stripe-shipping.md](stripe-shipping.md)

## See Also

- [Customer Service Documentation](stripe-customers-service.md)
- [Quick Start Guide](../quick-start.md)
- [Testing Guide](../testing.md)
- [ISO 3166-1 Country Codes](https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2)
- [Stripe Address Object Reference](https://stripe.com/docs/api/customers/object#customer_object-address)