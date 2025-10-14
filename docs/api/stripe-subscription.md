# Stripe::subscription()

Creates a `StripeSubscription` data transfer object (DTO) for representing subscription information. This is a factory method that does not make any API calls - it simply creates a typed object that can be passed to service methods for managing recurring billing and subscriptions.

## Signature

```php
public static function subscription(mixed ...$params): StripeSubscription
```

## Parameters

All parameters are optional and use named argument syntax:

| Parameter              | Type                   | Description                                                        |
|------------------------|------------------------|--------------------------------------------------------------------|
| `id`                   | `?string`              | Stripe subscription ID (e.g., `sub_xxx`)                           |
| `customer`             | `?string`              | Customer ID the subscription belongs to                            |
| `status`               | `?SubscriptionStatus`  | Subscription status enum                                           |
| `currentPeriodStart`   | `?int`                 | Start of current billing period (Unix timestamp)                   |
| `currentPeriodEnd`     | `?int`                 | End of current billing period (Unix timestamp)                     |
| `cancelAt`             | `?int`                 | When subscription will be canceled (Unix timestamp)                |
| `canceledAt`           | `?int`                 | When subscription was canceled (Unix timestamp)                    |
| `trialStart`           | `?int`                 | Start of trial period (Unix timestamp)                             |
| `trialEnd`             | `?int`                 | End of trial period (Unix timestamp)                               |
| `items`                | `?array`               | Array of subscription items (prices and quantities)                |
| `defaultPaymentMethod` | `?string`              | Default payment method ID for this subscription                    |
| `metadata`             | `?array`               | Key-value metadata                                                 |
| `currency`             | `?string`              | Three-letter ISO currency code                                     |
| `collectionMethod`     | `?CollectionMethod`    | How to collect payment (charge_automatically or send_invoice)      |
| `billingCycleAnchor`   | `?int`                 | Billing cycle anchor (Unix timestamp)                              |
| `cancelAtPeriodEnd`    | `?bool`                | Whether to cancel at end of current period                         |
| `daysUntilDue`         | `?int`                 | Days until invoice payment is due (for send_invoice)               |
| `description`          | `?string`              | Description of the subscription                                    |

## Returns

`StripeSubscription` - An immutable data transfer object representing a Stripe subscription

## Usage Examples

### Basic Subscription

```php
use EncoreDigitalGroup\Stripe\Stripe;

$subscription = Stripe::subscription(
    customer: 'cus_xxx',
    items: [
        [
            'price' => 'price_xxx',
            'quantity' => 1
        ]
    ]
);
```

### Subscription with Multiple Items

```php
$subscription = Stripe::subscription(
    customer: 'cus_xxx',
    items: [
        [
            'price' => 'price_basic',
            'quantity' => 1
        ],
        [
            'price' => 'price_addon',
            'quantity' => 3
        ]
    ]
);
```

### Subscription with Trial Period

```php
$subscription = Stripe::subscription(
    customer: 'cus_xxx',
    items: [
        ['price' => 'price_xxx', 'quantity' => 1]
    ],
    trialEnd: strtotime('+14 days')
);
```

### Subscription with Metadata

```php
$subscription = Stripe::subscription(
    customer: 'cus_xxx',
    items: [
        ['price' => 'price_xxx', 'quantity' => 1]
    ],
    metadata: [
        'plan_name' => 'Premium',
        'team_id' => '12345',
        'source' => 'web_signup'
    ]
);
```

### Invoice-Based Subscription

```php
use EncoreDigitalGroup\Stripe\Enums\CollectionMethod;

$subscription = Stripe::subscription(
    customer: 'cus_xxx',
    items: [
        ['price' => 'price_enterprise', 'quantity' => 1]
    ],
    collectionMethod: CollectionMethod::SendInvoice,
    daysUntilDue: 30
);
```

### Subscription with Specific Payment Method

```php
$subscription = Stripe::subscription(
    customer: 'cus_xxx',
    items: [
        ['price' => 'price_xxx', 'quantity' => 1]
    ],
    defaultPaymentMethod: 'pm_xxx'
);
```

## Using with Services

The `StripeSubscription` object is typically passed to service methods:

### Creating a Subscription

```php
// Create the subscription object
$subscriptionData = Stripe::subscription(
    customer: 'cus_xxx',
    items: [
        ['price' => 'price_monthly', 'quantity' => 1]
    ]
);

// Send to Stripe API
$createdSubscription = Stripe::subscriptions()->create($subscriptionData);

echo "Created subscription: {$createdSubscription->id}";
echo "Status: {$createdSubscription->status->value}";
```

### Updating a Subscription

```php
// Build update data
$updateData = Stripe::subscription(
    items: [
        ['price' => 'price_annual', 'quantity' => 1]
    ]
);

// Update via API
$updatedSubscription = Stripe::subscriptions()->update('sub_xxx', $updateData);
```

### Chained Pattern

```php
// Most common: chain factory and service call
$subscription = Stripe::subscriptions()->create(
    Stripe::subscription(
        customer: 'cus_xxx',
        items: [
            ['price' => 'price_xxx', 'quantity' => 1]
        ]
    )
);
```

## Subscription Status Enum

The `status` parameter uses the `SubscriptionStatus` enum:

```php
use EncoreDigitalGroup\Stripe\Enums\SubscriptionStatus;

$subscription = Stripe::subscription(
    customer: 'cus_xxx',
    status: SubscriptionStatus::Active
);
```

Available statuses:
- `SubscriptionStatus::Incomplete` - Payment pending
- `SubscriptionStatus::IncompleteExpired` - Payment failed and subscription expired
- `SubscriptionStatus::Trialing` - In trial period
- `SubscriptionStatus::Active` - Active and paid
- `SubscriptionStatus::PastDue` - Payment failed but retrying
- `SubscriptionStatus::Canceled` - Canceled
- `SubscriptionStatus::Unpaid` - Payment failed and no longer retrying

## Collection Method Enum

The `collectionMethod` parameter uses the `CollectionMethod` enum:

```php
use EncoreDigitalGroup\Stripe\Enums\CollectionMethod;

// Automatically charge
$subscription = Stripe::subscription(
    customer: 'cus_xxx',
    collectionMethod: CollectionMethod::ChargeAutomatically
);

// Send invoice
$subscription = Stripe::subscription(
    customer: 'cus_xxx',
    collectionMethod: CollectionMethod::SendInvoice,
    daysUntilDue: 30
);
```

## Subscription Items Structure

The `items` array contains subscription line items:

```php
$subscription = Stripe::subscription(
    customer: 'cus_xxx',
    items: [
        [
            'price' => 'price_xxx',      // Required: Stripe price ID
            'quantity' => 2,             // Optional: defaults to 1
            'metadata' => [              // Optional: item-level metadata
                'description' => 'Base subscription'
            ]
        ],
        [
            'price' => 'price_addon',
            'quantity' => 5
        ]
    ]
);
```

When retrieved from Stripe, items also contain:
- `id` - Subscription item ID
- `price` - Full price object (not just ID)

## Object Properties

The `StripeSubscription` object exposes all parameters as public readonly properties:

```php
$subscription = Stripe::subscription(
    customer: 'cus_xxx',
    items: [
        ['price' => 'price_xxx', 'quantity' => 1]
    ],
    status: SubscriptionStatus::Active
);

echo $subscription->customer;           // "cus_xxx"
echo $subscription->status->value;      // "active"
print_r($subscription->items);          // Array of items
```

## Data Conversion

### To Array

Convert the subscription object to an array for API requests:

```php
$subscription = Stripe::subscription(
    customer: 'cus_xxx',
    items: [
        ['price' => 'price_xxx', 'quantity' => 1]
    ]
);

$array = $subscription->toArray();
// [
//     "customer" => "cus_xxx",
//     "items" => [
//         ["price" => "price_xxx", "quantity" => 1]
//     ]
// ]
// Note: null values are filtered out
```

### From Stripe Object

Convert a Stripe SDK subscription object to a typed DTO:

```php
use Stripe\Subscription as StripeSubscriptionSDK;

// After receiving from Stripe API
$sdkSubscription = $stripeClient->subscriptions->retrieve('sub_xxx');

// Convert to our DTO
$subscription = StripeSubscription::fromStripeObject($sdkSubscription);
```

## Common Patterns

### Create Subscription with Trial

```php
$subscription = Stripe::subscriptions()->create(
    Stripe::subscription(
        customer: $user->stripe_customer_id,
        items: [
            ['price' => 'price_premium', 'quantity' => 1]
        ],
        trialEnd: strtotime('+30 days'),
        metadata: [
            'user_id' => $user->id,
            'plan' => 'premium'
        ]
    )
);
```

### Upgrade/Downgrade Subscription

```php
// Get current subscription
$current = Stripe::subscriptions()->get('sub_xxx');

// Update with new price
$upgraded = Stripe::subscriptions()->update(
    $current->id,
    Stripe::subscription(
        items: [
            ['price' => 'price_enterprise', 'quantity' => 1]
        ]
    )
);
```

### Cancel at Period End

```php
$subscription = Stripe::subscriptions()->cancelAtPeriodEnd('sub_xxx');

// Or create with this setting
$subscription = Stripe::subscription(
    customer: 'cus_xxx',
    items: [['price' => 'price_xxx', 'quantity' => 1]],
    cancelAtPeriodEnd: true
);
```

### Metered Billing

```php
$subscription = Stripe::subscription(
    customer: 'cus_xxx',
    items: [
        [
            'price' => 'price_metered', // Usage-based price
            'quantity' => 0              // Will be updated via usage records
        ]
    ]
);
```

## Type Safety

The `StripeSubscription` object provides compile-time type safety:

```php
// IDE autocomplete and type checking
$subscription = Stripe::subscription(
    customer: 'cus_xxx',
    // IDE will suggest: items, status, trialEnd, metadata, etc.
);

// Type errors caught at static analysis
$subscription = Stripe::subscription(
    customer: 123 // ❌ PHPStan error: expected string, got int
);
```

## Validation

### Required Fields for Creation

When creating a subscription via API, certain fields are required:
- `customer` - Must be a valid Stripe customer ID
- `items` - Must contain at least one item with a valid price

```php
// ✅ Valid
$subscription = Stripe::subscription(
    customer: 'cus_xxx',
    items: [['price' => 'price_xxx', 'quantity' => 1]]
);

// ❌ Missing required fields will fail at API level
$subscription = Stripe::subscription(
    customer: 'cus_xxx'
    // Missing items
);
```

## Integration Examples

### Laravel User Model

```php
class User extends Authenticatable
{
    public function subscribe(string $priceId): StripeSubscription
    {
        return Stripe::subscriptions()->create(
            Stripe::subscription(
                customer: $this->stripe_customer_id,
                items: [
                    ['price' => $priceId, 'quantity' => 1]
                ],
                metadata: [
                    'user_id' => $this->id,
                    'email' => $this->email
                ]
            )
        );
    }

    public function hasActiveSubscription(): bool
    {
        if (!$this->stripe_subscription_id) {
            return false;
        }

        $subscription = Stripe::subscriptions()->get($this->stripe_subscription_id);

        return $subscription->status === SubscriptionStatus::Active;
    }
}
```

### Controller Example

```php
class SubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'price_id' => 'required|string',
            'quantity' => 'integer|min:1'
        ]);

        try {
            $subscription = Stripe::subscriptions()->create(
                Stripe::subscription(
                    customer: $request->user()->stripe_customer_id,
                    items: [
                        [
                            'price' => $validated['price_id'],
                            'quantity' => $validated['quantity'] ?? 1
                        ]
                    ]
                )
            );

            return response()->json([
                'subscription' => $subscription,
                'message' => 'Subscription created successfully'
            ], 201);

        } catch (ApiErrorException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
```

## Testing

### Using Stripe::fake()

```php
use Tests\Support\StripeFixtures;
use EncoreDigitalGroup\Stripe\Stripe;

test('creates a subscription', function () {
    $fake = Stripe::fake([
        'subscriptions.create' => StripeFixtures::subscription([
            'id' => 'sub_test123',
            'customer' => 'cus_test'
        ])
    ]);

    $subscription = Stripe::subscriptions()->create(
        Stripe::subscription(
            customer: 'cus_test',
            items: [
                ['price' => 'price_test', 'quantity' => 1]
            ]
        )
    );

    expect($subscription->id)->toBe('sub_test123')
        ->and($subscription->customer)->toBe('cus_test')
        ->and($fake)->toHaveCalledStripeMethod('subscriptions.create');
});
```

## Related Documentation

- **StripeSubscriptionService** - Subscription CRUD operations: [stripe-subscriptions-service.md](stripe-subscriptions-service.md)
- **StripeCustomer** - Customer object: [stripe-customer.md](stripe-customer.md)
- **StripePrice** - Price object: [stripe-price.md](stripe-price.md)
- **Stripe Facade** - Main entry point: [stripe-facade.md](stripe-facade.md)

## See Also

- [Quick Start Guide](../quick-start.md)
- [Testing Guide](../testing.md)
- [Subscription Status Reference](../enums.md#subscription-status)
- [Stripe Subscription API Reference](https://stripe.com/docs/api/subscriptions)