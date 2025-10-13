# Stripe::webhook()

Creates a `StripeWebhook` data transfer object (DTO) for managing Stripe webhook endpoints. Webhooks allow your application to receive real-time notifications about
events in your Stripe account, such as successful payments, failed charges, or customer updates.

## Signature

```php
public static function webhook(mixed ...$params): StripeWebhook
```

## Parameters

| Parameter | Type     | Required | Description                                                      |
|-----------|----------|----------|------------------------------------------------------------------|
| `url`     | `string` | Yes      | The HTTPS endpoint URL that will receive webhook events          |
| `events`  | `array`  | No       | Array of event types to subscribe to. Default: `[]` (all events) |

## Returns

`StripeWebhook` - An immutable data transfer object for webhook configuration

## Usage Examples

### Basic Webhook

```php
use EncoreDigitalGroup\Stripe\Stripe;

$webhook = Stripe::webhook(
    url: 'https://example.com/webhooks/stripe'
);
// Subscribes to all events
```

### Webhook with Specific Events

```php
$webhook = Stripe::webhook(
    url: 'https://example.com/webhooks/stripe',
    events: [
        'customer.created',
        'customer.updated',
        'customer.deleted'
    ]
);
```

### Payment-Related Events

```php
$webhook = Stripe::webhook(
    url: 'https://example.com/webhooks/payments',
    events: [
        'payment_intent.succeeded',
        'payment_intent.failed',
        'charge.succeeded',
        'charge.failed',
        'charge.refunded'
    ]
);
```

### Subscription Events

```php
$webhook = Stripe::webhook(
    url: 'https://example.com/webhooks/subscriptions',
    events: [
        'customer.subscription.created',
        'customer.subscription.updated',
        'customer.subscription.deleted',
        'invoice.paid',
        'invoice.payment_failed'
    ]
);
```

## Object Properties

The `StripeWebhook` object exposes its parameters as public readonly properties:

```php
$webhook = Stripe::webhook(
    url: 'https://example.com/webhooks/stripe',
    events: ['customer.created']
);

echo $webhook->url;                // "https://example.com/webhooks/stripe"
print_r($webhook->events);         // ['customer.created']
```

## Data Conversion

### To Array

Convert the webhook object to an array for Stripe API requests:

```php
$webhook = Stripe::webhook(
    url: 'https://example.com/webhooks/stripe',
    events: ['customer.created', 'invoice.paid']
);

$array = $webhook->toArray();
// [
//     "url" => "https://example.com/webhooks/stripe",
//     "enabled_events" => ["customer.created", "invoice.paid"]
// ]
```

Note: The array format uses `enabled_events` to match Stripe's API naming convention.

## Receiving Webhooks

### Controller Setup

```php
use EncoreDigitalGroup\Stripe\Objects\Support\StripeWebhook;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $signature = StripeWebhook::getWebhookSignatureHeader();
        $secret = config('stripe.webhook_secret');

        try {
            $event = StripeWebhook::fromRequest($payload, $signature, $secret);

            // Handle the event
            match ($event->type) {
                'customer.created' => $this->handleCustomerCreated($event),
                'invoice.paid' => $this->handleInvoicePaid($event),
                'payment_intent.succeeded' => $this->handlePaymentSucceeded($event),
                default => logger()->info("Unhandled event: {$event->type}")
            };

            return response()->json(['status' => 'success']);

        } catch (SignatureVerificationException $e) {
            logger()->error('Invalid webhook signature', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }
    }

    protected function handleCustomerCreated($event): void
    {
        $customer = $event->data->object;
        logger()->info('Customer created', ['customer_id' => $customer->id]);

        // Your business logic here
    }

    protected function handleInvoicePaid($event): void
    {
        $invoice = $event->data->object;
        logger()->info('Invoice paid', ['invoice_id' => $invoice->id]);

        // Your business logic here
    }

    protected function handlePaymentSucceeded($event): void
    {
        $paymentIntent = $event->data->object;
        logger()->info('Payment succeeded', ['payment_intent_id' => $paymentIntent->id]);

        // Your business logic here
    }
}
```

### Route Registration

```php
// routes/api.php
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle']);
```

### CSRF Exception

Webhooks must bypass CSRF protection:

```php
// app/Http/Middleware/VerifyCsrfToken.php
protected $except = [
    'webhooks/stripe',
];
```

## Static Helper Methods

### Get Webhook Signature Header

Retrieves the Stripe signature from the request headers:

```php
$signature = StripeWebhook::getWebhookSignatureHeader();
```

This is equivalent to:

```php
$signature = request()->header('stripe-signature');
```

### Verify and Parse Webhook

Verifies the webhook signature and constructs the event object:

```php
use Stripe\Event;

$payload = request()->getContent();
$signature = StripeWebhook::getWebhookSignatureHeader();
$secret = config('stripe.webhook_secret');

try {
    $event = StripeWebhook::fromRequest($payload, $signature, $secret);

    // $event is now a verified Stripe\Event object
    echo $event->type;        // e.g., "customer.created"
    $data = $event->data->object;

} catch (SignatureVerificationException $e) {
    // Invalid signature - potential security issue
    abort(400, 'Invalid webhook signature');
}
```

## Common Event Types

### Customer Events

```php
$webhook = Stripe::webhook(
    url: 'https://example.com/webhooks/stripe',
    events: [
        'customer.created',
        'customer.updated',
        'customer.deleted',
        'customer.source.created',
        'customer.source.updated',
        'customer.source.deleted'
    ]
);
```

### Payment Intent Events

```php
$webhook = Stripe::webhook(
    url: 'https://example.com/webhooks/stripe',
    events: [
        'payment_intent.created',
        'payment_intent.succeeded',
        'payment_intent.failed',
        'payment_intent.canceled'
    ]
);
```

### Charge Events

```php
$webhook = Stripe::webhook(
    url: 'https://example.com/webhooks/stripe',
    events: [
        'charge.succeeded',
        'charge.failed',
        'charge.refunded',
        'charge.dispute.created'
    ]
);
```

### Subscription Events

```php
$webhook = Stripe::webhook(
    url: 'https://example.com/webhooks/stripe',
    events: [
        'customer.subscription.created',
        'customer.subscription.updated',
        'customer.subscription.deleted',
        'customer.subscription.trial_will_end'
    ]
);
```

### Invoice Events

```php
$webhook = Stripe::webhook(
    url: 'https://example.com/webhooks/stripe',
    events: [
        'invoice.created',
        'invoice.finalized',
        'invoice.paid',
        'invoice.payment_failed',
        'invoice.upcoming'
    ]
);
```

## Security Best Practices

### 1. Always Verify Signatures

Never process webhooks without signature verification:

```php
// ✅ Good: Verify signature
try {
    $event = StripeWebhook::fromRequest($payload, $signature, $secret);
    // Process event
} catch (SignatureVerificationException $e) {
    abort(400);
}

// ❌ Bad: No verification
$event = json_decode($payload);
// This could be forged!
```

### 2. Use HTTPS

Stripe only sends webhooks to HTTPS endpoints in production:

```php
// ✅ Production
$webhook = Stripe::webhook(
    url: 'https://example.com/webhooks/stripe'
);

// ❌ Development only
$webhook = Stripe::webhook(
    url: 'http://localhost/webhooks/stripe'
);
```

### 3. Idempotency

Webhooks may be sent multiple times. Make your handlers idempotent:

```php
protected function handleInvoicePaid($event): void
{
    $invoice = $event->data->object;

    // Use unique identifiers to prevent duplicate processing
    if (ProcessedWebhook::where('event_id', $event->id)->exists()) {
        logger()->info('Webhook already processed', ['event_id' => $event->id]);
        return;
    }

    // Process the invoice
    DB::transaction(function () use ($invoice, $event) {
        // Your business logic
        ProcessedWebhook::create(['event_id' => $event->id]);
    });
}
```

### 4. Store Webhook Secret Securely

Use environment variables for the webhook signing secret:

```php
// .env
STRIPE_WEBHOOK_SECRET=whsec_xxx

// config/stripe.php
return [
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
];
```

## Testing Webhooks

### Local Testing with Stripe CLI

```bash
# Install Stripe CLI
stripe listen --forward-to localhost:8000/webhooks/stripe

# Trigger specific events
stripe trigger customer.created
stripe trigger payment_intent.succeeded
stripe trigger invoice.paid
```

### Testing in PHPUnit

```php
use EncoreDigitalGroup\Stripe\Objects\Support\StripeWebhook;
use Stripe\Event;
use Stripe\Webhook;

test('handles customer created webhook', function () {
    $payload = json_encode([
        'type' => 'customer.created',
        'data' => [
            'object' => [
                'id' => 'cus_test123',
                'email' => 'test@example.com'
            ]
        ]
    ]);

    // Mock the signature (in real tests, use Stripe CLI webhook secret)
    $secret = 'whsec_test';
    $signature = Webhook::generateTestHeaderString($payload, $secret);

    $response = $this->postJson('/webhooks/stripe', [], [
        'stripe-signature' => $signature,
    ]);

    $response->assertStatus(200);
});
```

### Using Stripe::fake()

```php
test('webhook object creation', function () {
    $webhook = Stripe::webhook(
        url: 'https://example.com/webhooks/stripe',
        events: ['customer.created']
    );

    expect($webhook->url)->toBe('https://example.com/webhooks/stripe')
        ->and($webhook->events)->toBe(['customer.created'])
        ->and($webhook->toArray())->toHaveKey('enabled_events');
});
```

## Error Handling

```php
use Stripe\Exception\SignatureVerificationException;

public function handle(Request $request)
{
    try {
        $payload = $request->getContent();
        $signature = StripeWebhook::getWebhookSignatureHeader();
        $secret = config('stripe.webhook_secret');

        $event = StripeWebhook::fromRequest($payload, $signature, $secret);

        // Process event
        $this->processEvent($event);

        return response()->json(['status' => 'success']);

    } catch (SignatureVerificationException $e) {
        // Invalid signature - possible attack
        logger()->warning('Invalid webhook signature', [
            'ip' => $request->ip(),
            'error' => $e->getMessage()
        ]);

        return response()->json(['error' => 'Invalid signature'], 400);

    } catch (\Exception $e) {
        // Processing error - return 500 so Stripe retries
        logger()->error('Webhook processing error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json(['error' => 'Processing failed'], 500);
    }
}
```

## Queue Processing

For long-running webhook handlers, use Laravel queues:

```php
public function handle(Request $request)
{
    $payload = $request->getContent();
    $signature = StripeWebhook::getWebhookSignatureHeader();
    $secret = config('stripe.webhook_secret');

    try {
        $event = StripeWebhook::fromRequest($payload, $signature, $secret);

        // Dispatch to queue immediately
        ProcessStripeWebhook::dispatch($event);

        // Return success quickly
        return response()->json(['status' => 'queued']);

    } catch (SignatureVerificationException $e) {
        return response()->json(['error' => 'Invalid signature'], 400);
    }
}
```

## Type Safety

The object provides compile-time type safety:

```php
$webhook = Stripe::webhook(
    url: 'https://example.com/webhooks/stripe',
    events: ['customer.created'] // IDE autocomplete for event types
);

// Type errors caught at static analysis
$webhook = Stripe::webhook(
    url: 123 // ❌ PHPStan error: expected string, got int
);
```

## See Also

- [Quick Start Guide](../quick-start.md)
- [Testing Guide](../testing.md)
- [Stripe Webhook Documentation](https://stripe.com/docs/webhooks)
- [Stripe CLI](https://stripe.com/docs/stripe-cli)
- [Event Types Reference](https://stripe.com/docs/api/events/types)