# Stripe for Laravel

A clean, type-safe PHP interface for the Stripe API designed specifically for Laravel applications. This library wraps the Stripe PHP SDK with strongly-typed objects,
service classes, and enums while maintaining full compatibility with Laravel 11-12.

## Why This Library?

The official Stripe PHP SDK, while powerful, returns dynamic objects and arrays that lack type safety and IDE support. This library bridges that gap by providing:

- **Type Safety**: All Stripe objects are represented as strongly-typed PHP classes with full PHPStan Level 8 compliance
- **Laravel Integration**: Seamless integration with Laravel's service container and testing infrastructure
- **Developer Experience**: Rich IDE autocompletion, type hints, and inline documentation
- **Consistent API**: Clean, predictable methods that follow Laravel conventions
- **Comprehensive Testing**: Built-in testing utilities that fake Stripe API calls without network requests
- **Stripe SDK Escape Hatch**: Direct access to the Stripe SDK for when you need to do something super custom, that this SDK does not directly support.

## Requirements

- PHP 8.3+
- Laravel 11 or 12
- Stripe PHP SDK ^18.0

## Installation

Install via Composer:

```bash
composer require encoredigitalgroup/stripe
```

The package will automatically register its service provider with Laravel's auto-discovery feature.

## Quick Start

Configure your Stripe secret key in `.env`:

```env
STRIPE_SECRET_KEY=sk_test_...
STRIPE_PUBLISHABLE_KEY=pk_test_...
```

Start using the library:

```php
use EncoreDigitalGroup\Stripe\Stripe;

// Create a customer
$customer = Stripe::customer()
    ->withEmail('customer@example.com')
    ->withName('John Doe')
    ->save();

echo $customer->id(); // cus_...
```

## Core Features

### Type-Safe Objects with Fluent API

All objects use a fluent API pattern with private properties and chainable `withXXX()` methods. Access DTOs through the `Stripe` facade:

```php
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Objects\Product\{StripeProduct, StripePrice, StripeRecurring};
use EncoreDigitalGroup\Stripe\Objects\Support\StripeAddress;
use EncoreDigitalGroup\Stripe\Objects\Customer\StripeShipping;

// Customer with full details
$customer = Stripe::customer()
    ->withEmail('customer@example.com')
    ->withName('John Doe')
    ->withPhone('+1-555-123-4567')
    ->withAddress(
        StripeAddress::make()
            ->withLine1('123 Main St')
            ->withCity('San Francisco')
            ->withState('CA')
            ->withPostalCode('94105')
            ->withCountry('US')
    );

// Subscription with trial
$subscription = Stripe::subscription()
    ->withCustomer('cus_123')
    ->withItems([['price' => 'price_monthly', 'quantity' => 1]])
    ->withTrialEnd(now()->addDays(14));

// Webhook endpoint
$endpoint = Stripe::webhook()
    ->withUrl('https://myapp.com/webhooks/stripe')
    ->withEnabledEvents(['customer.created', 'invoice.paid'])
    ->withDescription('Production webhook');
```

### Enums for Type Safety

String-backed enums prevent typos and provide IDE autocompletion:

```php
use EncoreDigitalGroup\Stripe\Enums\{
    RecurringInterval,
    PriceType,
    SubscriptionStatus,
    CollectionMethod,
    ProrationBehavior
};

// All enum cases use PascalCase
RecurringInterval::Month
RecurringInterval::Year
PriceType::Recurring
SubscriptionStatus::Active
```

### Comprehensive Testing Infrastructure

Test your Stripe integration without making real API calls:

```php
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Support\Testing\{StripeFixtures, StripeMethod};

test('can create a customer', function () {
    // Set up fake responses
    $fake = Stripe::fake([
        StripeMethod::CustomersCreate->value => StripeFixtures::customer([
            'id' => 'cus_test123',
            'email' => 'test@example.com'
        ])
    ]);

    // Execute code under test
    $customer = Stripe::customer()
        ->withEmail('test@example.com')
        ->withName('Test Customer')
        ->save();

    // Assert results
    expect($customer->id())->toBe('cus_test123')
        ->and($fake)->toHaveCalledStripeMethod(StripeMethod::CustomersCreate);
});
```

## Common Use Cases

### Customer Management

```php
use EncoreDigitalGroup\Stripe\Stripe;

// Create customer
$customer = Stripe::customer()
    ->withEmail('customer@example.com')
    ->withName('Jane Smith')
    ->withMetadata(['user_id' => '12345'])
    ->save();

// Retrieve customer
$customer = Stripe::customer()->get('cus_123');

// Update customer
$updated = Stripe::customer()
    ->get('cus_123')
    ->withName('Jane Doe')
    ->save();
```

### Product & Price Management

```php
use EncoreDigitalGroup\Stripe\Objects\Product\{StripeProduct, StripePrice, StripeRecurring};
use EncoreDigitalGroup\Stripe\Enums\{PriceType, RecurringInterval};

// Create product
$product = StripeProduct::make()
    ->withName('Premium Subscription')
    ->withDescription('Access to all premium features')
    ->save();

// Create recurring price with strongly-typed recurring object
$price = StripePrice::make()
    ->withProduct($product->id())
    ->withCurrency('usd')
    ->withUnitAmount(2999) // $29.99
    ->withType(PriceType::Recurring)
    ->withRecurring(
        StripeRecurring::make()
            ->withInterval(RecurringInterval::Month)
            ->withIntervalCount(1)
    )
    ->save();

// Archive product (soft delete)
$archived = StripeProduct::make()
    ->get('prod_123')
    ->archive();

// Reactivate product
$active = StripeProduct::make()
    ->get('prod_123')
    ->reactivate();
```

### Subscription Management

```php
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Enums\ProrationBehavior;

// Create subscription
$subscription = Stripe::subscription()
    ->withCustomer('cus_123')
    ->withItems([
        ['price' => 'price_monthly', 'quantity' => 1]
    ])
    ->withMetadata(['plan' => 'professional'])
    ->save();

// Update subscription (upgrade/downgrade)
$updated = Stripe::subscription()
    ->get('sub_123')
    ->withItems([['price' => 'price_premium']])
    ->withProrationBehavior(ProrationBehavior::CreateProrations)
    ->save();

// Cancel subscription at period end
$canceled = Stripe::subscription()
    ->get('sub_123')
    ->cancelAtPeriodEnd()
    ->save();

// Cancel immediately
$canceled = Stripe::subscription()
    ->get('sub_123')
    ->cancelImmediately()
    ->save();

// Resume canceled subscription
$resumed = Stripe::subscription()
    ->get('sub_123')
    ->resume()
    ->save();
```

### Subscription Schedules

Plan complex subscription changes over time:

```php
use EncoreDigitalGroup\Stripe\Objects\Subscription\{StripeSubscription, Schedules\StripeSubscriptionSchedule, Schedules\StripePhaseItem};

// Access schedule from subscription and add phases
$subscription = StripeSubscription::make()->get('sub_123');

$subscription->schedule()
    ->get()
    ->addPhase(
        StripePhaseItem::make()
            ->withPrice('price_intro')
            ->withQuantity(1)
    )
    ->addPhase(
        StripePhaseItem::make()
            ->withPrice('price_regular')
            ->withQuantity(1)
    )
    ->save();

// Or create a standalone schedule
$schedule = StripeSubscriptionSchedule::make()
    ->withCustomer('cus_123')
    ->withStartDate(now()->addDay())
    ->save();
```

### Webhook Management

```php
use EncoreDigitalGroup\Stripe\Stripe;

// Create webhook endpoint
$endpoint = Stripe::webhook()
    ->withUrl(route('stripe.webhook'))
    ->withEnabledEvents([
        'customer.created',
        'customer.updated',
        'invoice.paid',
        'invoice.payment_failed',
        'customer.subscription.created',
        'customer.subscription.updated',
        'customer.subscription.deleted'
    ])
    ->withDescription('Production webhook')
    ->save();

// Store the webhook secret (IMPORTANT!)
$webhookSecret = $endpoint->secret();
```

### Webhook Processing

```php
use EncoreDigitalGroup\Stripe\Support\StripeWebhookHelper;
use EncoreDigitalGroup\Stripe\Objects\Customer\StripeCustomer;
use Illuminate\Http\Request;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        try {
            // Verify and construct event
            $event = StripeWebhookHelper::constructEvent(
                $request->getContent(),
                StripeWebhookHelper::getSignatureHeader(),
                config('services.stripe.webhook_secret')
            );

            // Process event
            match ($event->type) {
                'customer.created' => $this->handleCustomerCreated($event),
                'invoice.paid' => $this->handleInvoicePaid($event),
                default => logger()->info('Unhandled event', ['type' => $event->type])
            };

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }
    }

    protected function handleCustomerCreated($event): void
    {
        $customer = StripeCustomer::fromStripeObject($event->data->object);

        User::updateOrCreate(
            ['stripe_customer_id' => $customer->id()],
            ['email' => $customer->email(), 'name' => $customer->name()]
        );
    }
}
```

### Financial Connections

Enable secure bank account connections:

```php
use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeFinancialConnection;
use Stripe\StripeClient;

// Create financial connection session
$stripe = app(StripeClient::class);

$connection = StripeFinancialConnection::make()
    ->withAccountHolder(['type' => 'customer', 'customer' => 'cus_123'])
    ->withPermissions(['transactions', 'payment_method']);

$session = $stripe->financialConnections->sessions->create(
    $connection->toArray()
);
```

## Testing

Run the test suite:

```bash
# All tests
./vendor/bin/pest

# Specific suite
./vendor/bin/pest tests/Feature/
./vendor/bin/pest tests/Unit/

# With coverage
./vendor/bin/pest --coverage --min=80

# Stop on first failure
./vendor/bin/pest --stop-on-failure
```

## Code Quality

```bash
# Static analysis (Level 8)
./vendor/bin/phpstan analyse

# Code style fixing
./vendor/bin/duster fix

# Refactoring
./vendor/bin/rector process
```

## Contributing

Contributions to this repository are governed by the Encore Digital Group [Contribution Terms](https://docs.encoredigitalgroup.com/Contributing/Terms/).

Additional details on how to contribute are available [here](https://docs.encoredigitalgroup.com/Contributing/).

## License

This repository is licensed using a modified version of the BSD 3-Clause License.

The license is available for review [here](https://docs.encoredigitalgroup.com/LicenseTerms/).