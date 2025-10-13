# Stripe::customers()

Returns the `StripeCustomerService` instance for performing CRUD operations on Stripe customers. This service provides methods for creating, retrieving, updating,
deleting, listing, and searching customers via the Stripe API.

## Signature

```php
public static function customers(): StripeCustomerService
```

## Returns

`StripeCustomerService` - Service instance for customer operations

## Service Methods

### create()

Creates a new customer in Stripe.

#### Signature

```php
public function create(StripeCustomer $customer): StripeCustomer
```

#### Parameters

| Parameter  | Type             | Description                       |
|------------|------------------|-----------------------------------|
| `customer` | `StripeCustomer` | Customer data object (without ID) |

#### Returns

`StripeCustomer` - The created customer with Stripe-assigned ID

#### Throws

`Stripe\Exception\ApiErrorException` - If the API request fails

#### Example

```php
use EncoreDigitalGroup\Stripe\Stripe;

$customer = Stripe::customers()->create(
    Stripe::customer(
        email: 'customer@example.com',
        name: 'John Doe',
        phone: '+1-555-0123'
    )
);

echo "Created customer: {$customer->id}";
// Output: Created customer: cus_xxx
```

---

### get()

Retrieves an existing customer by ID.

#### Signature

```php
public function get(string $customerId): StripeCustomer
```

#### Parameters

| Parameter    | Type     | Description                          |
|--------------|----------|--------------------------------------|
| `customerId` | `string` | Stripe customer ID (e.g., `cus_xxx`) |

#### Returns

`StripeCustomer` - The retrieved customer object

#### Throws

`Stripe\Exception\ApiErrorException` - If customer not found or API request fails

#### Example

```php
$customer = Stripe::customers()->get('cus_xxx');

echo $customer->name;  // "John Doe"
echo $customer->email; // "customer@example.com"
```

---

### update()

Updates an existing customer.

#### Signature

```php
public function update(string $customerId, StripeCustomer $customer): StripeCustomer
```

#### Parameters

| Parameter    | Type             | Description                         |
|--------------|------------------|-------------------------------------|
| `customerId` | `string`         | Stripe customer ID to update        |
| `customer`   | `StripeCustomer` | Customer data with fields to update |

#### Returns

`StripeCustomer` - The updated customer object

#### Throws

`Stripe\Exception\ApiErrorException` - If customer not found or API request fails

#### Example

```php
$updatedCustomer = Stripe::customers()->update(
    'cus_xxx',
    Stripe::customer(
        name: 'Jane Doe',
        phone: '+1-555-9999'
    )
);

echo "Updated: {$updatedCustomer->name}";
```

**Note**: Only include fields you want to update. Null/unset fields are not sent to Stripe.

---

### delete()

Deletes a customer from Stripe.

#### Signature

```php
public function delete(string $customerId): bool
```

#### Parameters

| Parameter    | Type     | Description                  |
|--------------|----------|------------------------------|
| `customerId` | `string` | Stripe customer ID to delete |

#### Returns

`bool` - `true` if deleted successfully, `false` otherwise

#### Throws

`Stripe\Exception\ApiErrorException` - If API request fails

#### Example

```php
$deleted = Stripe::customers()->delete('cus_xxx');

if ($deleted) {
    echo "Customer deleted successfully";
}
```

**Warning**: Deleting a customer is permanent and cannot be undone. Active subscriptions must be canceled first.

---

### list()

Lists all customers with optional filtering and pagination.

#### Signature

```php
public function list(array $params = []): Collection<StripeCustomer>
```

#### Parameters

| Parameter | Type    | Description                                            |
|-----------|---------|--------------------------------------------------------|
| `params`  | `array` | Optional query parameters for filtering and pagination |

#### Available Parameters

- `limit` (int) - Number of customers to return (default: 10, max: 100)
- `starting_after` (string) - Customer ID for pagination
- `ending_before` (string) - Customer ID for pagination
- `email` (string) - Filter by exact email match
- `created` (array) - Filter by creation date

#### Returns

`Collection<StripeCustomer>` - Laravel collection of customer objects

#### Throws

`Stripe\Exception\ApiErrorException` - If API request fails

#### Examples

**Basic Listing**

```php
$customers = Stripe::customers()->list();

foreach ($customers as $customer) {
    echo "{$customer->name} - {$customer->email}\n";
}
```

**With Limit**

```php
$customers = Stripe::customers()->list(['limit' => 50]);

echo "Retrieved {$customers->count()} customers";
```

**Pagination**

```php
// First page
$firstPage = Stripe::customers()->list(['limit' => 10]);

// Next page
$lastCustomerId = $firstPage->last()->id;
$secondPage = Stripe::customers()->list([
    'limit' => 10,
    'starting_after' => $lastCustomerId
]);
```

**Filter by Email**

```php
$customers = Stripe::customers()->list([
    'email' => 'specific@example.com'
]);
```

**Filter by Creation Date**

```php
$customers = Stripe::customers()->list([
    'created' => [
        'gte' => strtotime('2025-01-01'),
        'lte' => strtotime('2025-12-31')
    ]
]);
```

---

### search()

Searches customers using Stripe's search query syntax.

#### Signature

```php
public function search(string $query, array $params = []): Collection<StripeCustomer>
```

#### Parameters

| Parameter | Type     | Description                                  |
|-----------|----------|----------------------------------------------|
| `query`   | `string` | Stripe search query string                   |
| `params`  | `array`  | Optional additional parameters (limit, page) |

#### Returns

`Collection<StripeCustomer>` - Laravel collection of matching customers

#### Throws

`Stripe\Exception\ApiErrorException` - If API request fails

#### Query Syntax

Stripe search uses a powerful query language:

- `email:'test@example.com'` - Exact email match
- `name~'john'` - Name contains "john" (case-insensitive)
- `email~'@gmail.com'` - Email ends with @gmail.com
- `-email~'@spam.com'` - Email doesn't contain @spam.com
- `created>1640000000` - Created after timestamp
- `created<1640000000` - Created before timestamp

Combine with AND:

- `name~'john' AND email~'@example.com'`

Combine with OR:

- `email~'@gmail.com' OR email~'@yahoo.com'`

#### Examples

**Search by Email Domain**

```php
$customers = Stripe::customers()->search("email~'@gmail.com'");

foreach ($customers as $customer) {
    echo "{$customer->email}\n";
}
```

**Search by Name**

```php
$customers = Stripe::customers()->search("name~'john'");
```

**Complex Query**

```php
$customers = Stripe::customers()->search(
    "name~'premium' AND created>1672531200"
);
```

**With Limit**

```php
$customers = Stripe::customers()->search(
    "email~'@example.com'",
    ['limit' => 25]
);
```

**Exclude Domain**

```php
$customers = Stripe::customers()->search(
    "-email~'@disposable.com'"
);
```

## Usage Examples

### Complete CRUD Flow

```php
use EncoreDigitalGroup\Stripe\Stripe;

// Create
$customer = Stripe::customers()->create(
    Stripe::customer(
        email: 'new@example.com',
        name: 'New Customer'
    )
);

$customerId = $customer->id;

// Retrieve
$retrieved = Stripe::customers()->get($customerId);

// Update
$updated = Stripe::customers()->update(
    $customerId,
    Stripe::customer(phone: '+1-555-0000')
);

// Delete
$deleted = Stripe::customers()->delete($customerId);
```

### Service Instance Reuse

For multiple operations, store the service instance:

```php
$customerService = Stripe::customers();

// Create multiple customers
$customer1 = $customerService->create(
    Stripe::customer(email: 'user1@example.com')
);

$customer2 = $customerService->create(
    Stripe::customer(email: 'user2@example.com')
);

// List all
$all = $customerService->list(['limit' => 100]);
```

### Error Handling

```php
use Stripe\Exception\ApiErrorException;

try {
    $customer = Stripe::customers()->get('cus_invalid');
} catch (ApiErrorException $e) {
    if ($e->getError()->type === 'invalid_request_error') {
        logger()->error('Customer not found', [
            'customer_id' => 'cus_invalid',
            'message' => $e->getMessage()
        ]);
    }

    return response()->json(['error' => 'Customer not found'], 404);
}
```

### Paginating Through All Customers

```php
$allCustomers = collect();
$params = ['limit' => 100];

do {
    $customers = Stripe::customers()->list($params);
    $allCustomers = $allCustomers->merge($customers);

    if ($customers->count() === 100) {
        $params['starting_after'] = $customers->last()->id;
    } else {
        break;
    }
} while (true);

echo "Total customers: {$allCustomers->count()}";
```

### Conditional Updates

```php
$customer = Stripe::customers()->get('cus_xxx');

// Only update if email changed
if ($customer->email !== $newEmail) {
    $customer = Stripe::customers()->update(
        $customer->id,
        Stripe::customer(email: $newEmail)
    );
}
```

## Integration Patterns

### Laravel User Integration

```php
// User model
class User extends Authenticatable
{
    public function createStripeCustomer(): void
    {
        if ($this->stripe_customer_id) {
            return; // Already exists
        }

        $customer = Stripe::customers()->create(
            Stripe::customer(
                email: $this->email,
                name: $this->name
            )
        );

        $this->update(['stripe_customer_id' => $customer->id]);
    }

    public function syncToStripe(): void
    {
        if (!$this->stripe_customer_id) {
            $this->createStripeCustomer();
            return;
        }

        Stripe::customers()->update(
            $this->stripe_customer_id,
            Stripe::customer(
                email: $this->email,
                name: $this->name
            )
        );
    }
}
```

### Controller Example

```php
class CustomerController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'name' => 'required|string',
            'phone' => 'nullable|string'
        ]);

        try {
            $customer = Stripe::customers()->create(
                Stripe::customer(...$validated)
            );

            return response()->json([
                'customer' => $customer,
                'message' => 'Customer created successfully'
            ], 201);

        } catch (ApiErrorException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function show(string $customerId)
    {
        try {
            $customer = Stripe::customers()->get($customerId);
            return response()->json(['customer' => $customer]);

        } catch (ApiErrorException $e) {
            return response()->json(['error' => 'Customer not found'], 404);
        }
    }

    public function index(Request $request)
    {
        $customers = Stripe::customers()->list([
            'limit' => $request->input('limit', 10)
        ]);

        return response()->json(['customers' => $customers]);
    }
}
```

## Testing

### Using Stripe::fake()

```php
use Tests\Support\StripeFixtures;
use EncoreDigitalGroup\Stripe\Stripe;

test('creates a customer', function () {
    $fake = Stripe::fake([
        'customers.create' => StripeFixtures::customer([
            'id' => 'cus_test123',
            'email' => 'test@example.com'
        ])
    ]);

    $customer = Stripe::customers()->create(
        Stripe::customer(email: 'test@example.com')
    );

    expect($customer->id)->toBe('cus_test123')
        ->and($customer->email)->toBe('test@example.com')
        ->and($fake)->toHaveCalledStripeMethod('customers.create');
});

test('retrieves a customer', function () {
    Stripe::fake([
        'customers.retrieve' => StripeFixtures::customer([
            'id' => 'cus_test123',
            'name' => 'John Doe'
        ])
    ]);

    $customer = Stripe::customers()->get('cus_test123');

    expect($customer->name)->toBe('John Doe');
});

test('lists customers', function () {
    Stripe::fake([
        'customers.all' => StripeFixtures::customerList([
            StripeFixtures::customer(['id' => 'cus_1']),
            StripeFixtures::customer(['id' => 'cus_2']),
        ])
    ]);

    $customers = Stripe::customers()->list();

    expect($customers)->toHaveCount(2);
});
```

## Performance Considerations

### Caching

Cache frequently accessed customers:

```php
use Illuminate\Support\Facades\Cache;

function getCustomer(string $customerId): StripeCustomer
{
    return Cache::remember(
        "stripe.customer.{$customerId}",
        now()->addMinutes(30),
        fn() => Stripe::customers()->get($customerId)
    );
}
```

### Batch Operations

When processing many customers, use pagination efficiently:

```php
$customerService = Stripe::customers();
$params = ['limit' => 100];

do {
    $customers = $customerService->list($params);

    // Process batch
    foreach ($customers as $customer) {
        processCustomer($customer);
    }

    // Setup next page
    if ($customers->count() === 100) {
        $params['starting_after'] = $customers->last()->id;
    } else {
        break;
    }
} while (true);
```

## Type Safety

All service methods provide compile-time type safety:

```php
$service = Stripe::customers(); // StripeCustomerService

// IDE provides autocomplete
$customer = $service->create(/* ... */);

// Type errors caught by PHPStan
$service->get(123); // ❌ Expected string, got int
```

## Related Documentation

- **StripeCustomer Object** - Customer data structure: [stripe-customer.md](stripe-customer.md)
- **Stripe Facade** - Main entry point: [stripe-facade.md](stripe-facade.md)
- **Testing Guide** - Testing with fakes: [../testing.md](../testing.md)

## See Also

- [Quick Start Guide](../quick-start.md)
- [Error Handling](../advanced/error-handling.md)
- [Stripe Customer API Reference](https://stripe.com/docs/api/customers)