# Stripe::financialConnections()

Creates a `StripeFinancialConnection` data transfer object (DTO) for integrating with Stripe Financial Connections. This object is used to establish connections between
customers and their financial accounts (bank accounts, credit cards, etc.) for payment verification, balance checks, and transaction history.

## Signature

```php
public static function financialConnections(mixed ...$params): StripeFinancialConnection
```

## Parameters

| Parameter     | Type             | Required | Description                                              |
|---------------|------------------|----------|----------------------------------------------------------|
| `customer`    | `StripeCustomer` | Yes      | Customer object with a valid Stripe customer ID          |
| `permissions` | `array`          | No       | Array of permission strings. Default: `['transactions']` |

### Available Permissions

Financial Connections supports the following permissions:

- `transactions` - Access to transaction history
- `balances` - Access to account balances
- `ownership` - Verification of account ownership
- `payment_method` - Ability to use as payment method

## Returns

`StripeFinancialConnection` - An immutable data transfer object for financial connections

## Usage Examples

### Basic Financial Connection

```php
use EncoreDigitalGroup\Stripe\Stripe;

// Customer must have a Stripe ID
$customer = Stripe::customer(
    id: 'cus_xxx',
    email: 'customer@example.com'
);

$connection = Stripe::financialConnections(
    customer: $customer
);
// Default permissions: ['transactions']
```

### With Multiple Permissions

```php
$customer = Stripe::customer(id: 'cus_xxx');

$connection = Stripe::financialConnections(
    customer: $customer,
    permissions: ['transactions', 'balances', 'ownership']
);
```

### Creating Customer and Connection

```php
// Create customer first
$customer = Stripe::customers()->create(
    Stripe::customer(
        email: 'new@example.com',
        name: 'New Customer'
    )
);

// Then create financial connection
$connection = Stripe::financialConnections(
    customer: $customer,
    permissions: ['transactions', 'payment_method']
);
```

## Integration Flow

### Complete Connection Flow

```php
// 1. Create or retrieve customer
$customer = Stripe::customers()->get('cus_xxx');

// 2. Create financial connection configuration
$connection = Stripe::financialConnections(
    customer: $customer,
    permissions: ['transactions', 'balances', 'payment_method']
);

// 3. Create a Financial Connections Session via Stripe API
// (Session creation would use Stripe SDK directly or via a service)
$sessionData = $connection->toArray();

// 4. Frontend uses session to collect bank account
// User completes OAuth flow in your application

// 5. Retrieve connected accounts after user completes flow
// (This would be done via Stripe API after OAuth completion)
```

### Session Creation Example

```php
use Stripe\StripeClient;

$customer = Stripe::customer(id: 'cus_xxx');

$connection = Stripe::financialConnections(
    customer: $customer,
    permissions: ['transactions', 'payment_method']
);

// Use with Stripe SDK to create session
$stripe = new StripeClient(config('stripe.secret_key'));

$session = $stripe->financialConnections->sessions->create(
    $connection->toArray()
);

// Return session to frontend
return response()->json([
    'client_secret' => $session->client_secret
]);
```

## Object Properties

The `StripeFinancialConnection` object exposes its parameters as public readonly properties:

```php
$connection = Stripe::financialConnections(
    customer: Stripe::customer(id: 'cus_xxx'),
    permissions: ['transactions', 'balances']
);

echo $connection->customer->id;           // "cus_xxx"
print_r($connection->permissions);        // ['transactions', 'balances']
```

## Data Conversion

### To Array

Convert the connection object to an array for Stripe API requests:

```php
$connection = Stripe::financialConnections(
    customer: Stripe::customer(id: 'cus_xxx'),
    permissions: ['transactions']
);

$array = $connection->toArray();
// [
//     "account_holder" => [
//         "type" => "customer",
//         "customer" => "cus_xxx"
//     ],
//     "permissions" => ["transactions"]
// ]
```

The `toArray()` method automatically formats the data according to Stripe's Financial Connections API requirements, including the nested `account_holder` structure.

## Frontend Integration

### JavaScript/Vue/React Example

```php
// Backend: Create session
public function createFinancialConnectionSession(Request $request)
{
    $customer = Stripe::customers()->get($request->user()->stripe_customer_id);

    $connection = Stripe::financialConnections(
        customer: $customer,
        permissions: ['transactions', 'payment_method']
    );

    $stripe = new StripeClient(config('stripe.secret_key'));
    $session = $stripe->financialConnections->sessions->create(
        $connection->toArray()
    );

    return response()->json([
        'client_secret' => $session->client_secret
    ]);
}
```

```javascript
// Frontend: Use Stripe.js
const response = await fetch('/api/financial-connections/session');
const { client_secret } = await response.json();

const stripe = Stripe('pk_test_xxx');
const result = await stripe.collectFinancialConnectionsAccounts({
  clientSecret: client_secret
});

if (result.error) {
  console.error(result.error.message);
} else {
  console.log('Account connected:', result.financialConnectionsSession);
}
```

## Error Handling

### Missing Customer ID

The customer object must have a valid Stripe ID:

```php
// ❌ This will fail when converted to array
$customer = Stripe::customer(email: 'test@example.com'); // No ID

$connection = Stripe::financialConnections(customer: $customer);
$array = $connection->toArray(); // customer->id will be null
```

### Correct Usage

```php
// ✅ Customer with ID
$customer = Stripe::customers()->create(
    Stripe::customer(email: 'test@example.com')
);
// Now $customer->id is populated

$connection = Stripe::financialConnections(customer: $customer);
```

## Use Cases

### Payment Verification

Verify customer bank account for ACH payments:

```php
$connection = Stripe::financialConnections(
    customer: $customer,
    permissions: ['payment_method', 'ownership']
);
```

### Transaction Monitoring

Access transaction history for expense tracking:

```php
$connection = Stripe::financialConnections(
    customer: $customer,
    permissions: ['transactions', 'balances']
);
```

### Account Aggregation

Build financial dashboards:

```php
$connection = Stripe::financialConnections(
    customer: $customer,
    permissions: [
        'transactions',
        'balances',
        'ownership'
    ]
);
```

## Security Considerations

1. **Customer Ownership**: Always verify the customer belongs to the authenticated user:

```php
// ✅ Good: Verify ownership
$customer = Stripe::customers()->get($request->user()->stripe_customer_id);

// ❌ Bad: Using unverified customer ID
$customer = Stripe::customers()->get($request->input('customer_id'));
```

2. **Permission Scoping**: Only request necessary permissions:

```php
// ✅ Good: Minimal permissions
$connection = Stripe::financialConnections(
    customer: $customer,
    permissions: ['payment_method'] // Only what's needed
);

// ⚠️ Consider: Do you need all of these?
$connection = Stripe::financialConnections(
    customer: $customer,
    permissions: ['transactions', 'balances', 'ownership', 'payment_method']
);
```

3. **HTTPS Required**: Financial Connections requires HTTPS in production

4. **Client Secret Handling**: Never expose client secrets in URLs or logs

## Type Safety

The object provides compile-time type safety:

```php
$connection = Stripe::financialConnections(
    customer: Stripe::customer(id: 'cus_xxx'),
    permissions: ['transactions'] // IDE autocomplete
);

// Type errors caught at static analysis
$connection = Stripe::financialConnections(
    customer: 'cus_xxx' // ❌ PHPStan error: expected StripeCustomer, got string
);
```

## Testing

### Using Stripe::fake()

```php
use Tests\Support\StripeFixtures;

test('creates financial connection session', function () {
    $fake = Stripe::fake([
        'customers.retrieve' => StripeFixtures::customer(['id' => 'cus_test']),
    ]);

    $customer = Stripe::customers()->get('cus_test');

    $connection = Stripe::financialConnections(
        customer: $customer,
        permissions: ['transactions']
    );

    expect($connection->customer->id)->toBe('cus_test')
        ->and($connection->permissions)->toBe(['transactions']);
});
```

## Related Objects

- **StripeCustomer** - Required for creating connections: [stripe-customer.md](stripe-customer.md)
- **StripeBankAccount** - Represents connected bank accounts

## Related Services

- **StripeCustomerService** - Customer management: [stripe-customers-service.md](stripe-customers-service.md)

## See Also

- [Quick Start Guide](../quick-start.md)
- [Testing Guide](../testing.md)
- [Stripe Financial Connections Documentation](https://stripe.com/docs/financial-connections)
- [Stripe.js Financial Connections](https://stripe.com/docs/js/financial_connections)