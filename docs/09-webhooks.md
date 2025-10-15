# Webhooks

Webhooks are how Stripe notifies your application about events that happen in your Stripe account—successful payments, failed charges, subscription updates, and more. This chapter covers everything you need to handle Stripe webhooks in your Laravel application, from registration to verification and event processing.

## Table of Contents

- [Understanding Stripe Webhooks](#understanding-stripe-webhooks)
- [Webhook Configuration](#webhook-configuration)
- [Creating and Registering Webhooks](#creating-and-registering-webhooks)
- [Receiving and Verifying Webhooks](#receiving-and-verifying-webhooks)
- [Processing Webhook Events](#processing-webhook-events)
- [Common Webhook Events](#common-webhook-events)
- [Testing Webhooks](#testing-webhooks)
- [Common Patterns](#common-patterns)

## Understanding Stripe Webhooks

Webhooks are HTTP callbacks that Stripe sends to your application when specific events occur. This allows your application to respond to changes in real-time without polling Stripe's API.

### Why Use Webhooks?

```php
// ❌ Don't do this (polling is inefficient and slow)
while (true) {
    $subscription = Stripe::subscriptions()->get($subscriptionId);
    if ($subscription->status === SubscriptionStatus::Active) {
        // Process activation
        break;
    }
    sleep(5); // Wait and check again
}

// ✅ Do this (webhooks are instant and efficient)
// Stripe automatically notifies your application when subscription becomes active
public function handleWebhook(Request $request)
{
    $event = StripeWebhook::fromRequest(
        $request->getContent(),
        StripeWebhook::getWebhookSignatureHeader(),
        config('services.stripe.webhook_secret')
    );

    if ($event->type === 'customer.subscription.updated') {
        // Process subscription update immediately
        $this->processSubscriptionUpdate($event->data->object);
    }
}
```

### Webhook Flow

1. **Event Occurs** - Something happens in Stripe (payment succeeds, subscription cancels, etc.)
2. **Stripe Sends POST Request** - Stripe sends a POST request to your webhook endpoint
3. **Verify Signature** - Your application verifies the request came from Stripe
4. **Process Event** - Your application processes the event data
5. **Return 200 Response** - Your application returns a 200 status to acknowledge receipt

## Webhook Configuration

### Environment Variables

Add your webhook secret to your `.env` file:

```env
STRIPE_SECRET_KEY=sk_test_51abc...
STRIPE_WEBHOOK_SECRET=whsec_abc123...
```

The webhook secret is provided by Stripe when you create a webhook endpoint in the Stripe Dashboard or via API.

### Creating Webhook Endpoints

You'll need a publicly accessible URL for Stripe to send webhooks to. In development, you can use tools like:

- **Laravel Valet Share** - If using Valet
- **ngrok** - Tunnel to your local development server
- **Expose** - Laravel-friendly ngrok alternative

```bash
# Using ngrok
ngrok http 8000

# Using Expose (Laravel)
expose share http://localhost:8000
```

## Creating and Registering Webhooks

The `StripeWebhook` object helps you configure webhook endpoints.

### StripeWebhook Properties

```php
use EncoreDigitalGroup\Stripe\Stripe;

$webhook = Stripe::webhook(
    url: 'https://myapp.com/webhooks/stripe',  // string - Your webhook endpoint URL
    events: [                                   // array - Events to subscribe to
        'customer.created',
        'customer.updated',
        'invoice.paid',
        'invoice.payment_failed'
    ]
);
```

### Creating Webhooks via Stripe

There are three ways to create webhook objects:

#### Method 1: Direct DTO Creation

```php
use EncoreDigitalGroup\Stripe\Objects\Support\StripeWebhook;

$webhook = StripeWebhook::make(
    url: 'https://myapp.com/webhooks/stripe',
    events: ['customer.created', 'customer.updated']
);
```

#### Method 2: Using the Builder Pattern

```php
use EncoreDigitalGroup\Stripe\Stripe;

$webhook = Stripe::builder()->webhook()->build(
    url: 'https://myapp.com/webhooks/stripe',
    events: ['invoice.paid', 'invoice.payment_failed']
);
```

#### Method 3: Using the Facade Shortcut (Recommended)

```php
use EncoreDigitalGroup\Stripe\Stripe;

$webhook = Stripe::webhook(
    url: 'https://myapp.com/webhooks/stripe',
    events: ['customer.subscription.updated']
);
```

### Registering Webhooks with Stripe API

```php
use Stripe\StripeClient;
use EncoreDigitalGroup\Stripe\Stripe;

class WebhookSetupController extends Controller
{
    public function registerWebhook()
    {
        $stripe = app(StripeClient::class);

        $webhook = Stripe::webhook(
            url: route('stripe.webhook'),
            events: [
                'customer.created',
                'customer.updated',
                'customer.deleted',
                'invoice.paid',
                'invoice.payment_failed',
                'customer.subscription.created',
                'customer.subscription.updated',
                'customer.subscription.deleted',
                'payment_intent.succeeded',
                'payment_intent.payment_failed'
            ]
        );

        $webhookEndpoint = $stripe->webhookEndpoints->create($webhook->toArray());

        // Store the webhook secret
        // IMPORTANT: Save this secret - you'll need it to verify webhooks
        $webhookSecret = $webhookEndpoint->secret;

        return response()->json([
            'webhook_id' => $webhookEndpoint->id,
            'webhook_secret' => $webhookSecret
        ]);
    }
}
```

### Converting to Array

```php
$webhook = Stripe::webhook(
    url: 'https://myapp.com/webhooks/stripe',
    events: ['customer.created', 'invoice.paid']
);

$array = $webhook->toArray();

// Returns:
// [
//     'enabled_events' => ['customer.created', 'invoice.paid'],
//     'url' => 'https://myapp.com/webhooks/stripe'
// ]
```

## Receiving and Verifying Webhooks

Webhook verification is **critical** for security. Always verify that webhook requests actually come from Stripe.

### Basic Webhook Controller

```php
use EncoreDigitalGroup\Stripe\Objects\Support\StripeWebhook;
use Illuminate\Http\Request;
use Stripe\Event;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        try {
            // Get the webhook signature from request headers
            $signature = StripeWebhook::getWebhookSignatureHeader();

            // Verify and construct the event
            $event = StripeWebhook::fromRequest(
                $request->getContent(),
                $signature,
                config('services.stripe.webhook_secret')
            );

            // Process the event
            $this->processEvent($event);

            return response()->json(['status' => 'success']);

        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            logger()->error('Webhook signature verification failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Invalid signature'], 400);

        } catch (\Exception $e) {
            // Other errors
            logger()->error('Webhook processing failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    protected function processEvent(Event $event): void
    {
        // Handle different event types
        match ($event->type) {
            'customer.created' => $this->handleCustomerCreated($event),
            'customer.updated' => $this->handleCustomerUpdated($event),
            'invoice.paid' => $this->handleInvoicePaid($event),
            'invoice.payment_failed' => $this->handleInvoicePaymentFailed($event),
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($event),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event),
            default => logger()->info('Unhandled webhook event', ['type' => $event->type])
        };
    }
}
```

### Route Configuration

```php
// routes/api.php
use App\Http\Controllers\StripeWebhookController;

// Exempt from CSRF protection (add to VerifyCsrfToken middleware)
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handleWebhook'])
    ->name('stripe.webhook');
```

### Disabling CSRF Protection

Webhooks come from Stripe, not your users, so they won't have CSRF tokens. Add your webhook route to the CSRF exception list:

```php
// app/Http/Middleware/VerifyCsrfToken.php
namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        'webhooks/stripe',
        'api/webhooks/stripe',
    ];
}
```

## Processing Webhook Events

Each webhook event contains an `Event` object with a `type` and `data` property.

### Event Structure

```php
// $event is a \Stripe\Event object
$event->id;           // Event ID (e.g., "evt_abc123")
$event->type;         // Event type (e.g., "customer.created")
$event->data->object; // The Stripe object (Customer, Invoice, etc.)
$event->created;      // Unix timestamp of when event was created
$event->livemode;     // true for live mode, false for test mode
```

### Converting Stripe Objects to DTOs

```php
use EncoreDigitalGroup\Stripe\Objects\Customer\StripeCustomer;
use EncoreDigitalGroup\Stripe\Objects\Subscription\StripeSubscription;
use Stripe\Event;

protected function handleCustomerCreated(Event $event): void
{
    // Extract the Stripe customer object
    $stripeCustomerObject = $event->data->object;

    // Convert to our DTO
    $customer = StripeCustomer::fromStripeObject($stripeCustomerObject);

    // Now you have a fully-typed DTO to work with
    logger()->info('Customer created', [
        'customer_id' => $customer->id,
        'email' => $customer->email,
        'name' => $customer->name
    ]);

    // Create or update in your database
    \App\Models\User::updateOrCreate(
        ['stripe_customer_id' => $customer->id],
        [
            'email' => $customer->email,
            'name' => $customer->name
        ]
    );
}

protected function handleSubscriptionUpdated(Event $event): void
{
    $stripeSubscriptionObject = $event->data->object;

    // Convert to our DTO
    $subscription = StripeSubscription::fromStripeObject($stripeSubscriptionObject);

    // Handle subscription status changes
    if ($subscription->status === SubscriptionStatus::Active) {
        // Subscription is now active
        $this->activateUserAccess($subscription->customer);
    } elseif ($subscription->status === SubscriptionStatus::Canceled) {
        // Subscription was canceled
        $this->deactivateUserAccess($subscription->customer);
    }
}
```

## Common Webhook Events

Here are the most commonly used webhook events and how to handle them:

### Customer Events

```php
// customer.created - New customer was created
protected function handleCustomerCreated(Event $event): void
{
    $customer = StripeCustomer::fromStripeObject($event->data->object);

    User::updateOrCreate(
        ['stripe_customer_id' => $customer->id],
        ['email' => $customer->email, 'name' => $customer->name]
    );
}

// customer.updated - Customer information was updated
protected function handleCustomerUpdated(Event $event): void
{
    $customer = StripeCustomer::fromStripeObject($event->data->object);

    User::where('stripe_customer_id', $customer->id)
        ->update([
            'email' => $customer->email,
            'name' => $customer->name
        ]);
}

// customer.deleted - Customer was deleted
protected function handleCustomerDeleted(Event $event): void
{
    $customerId = $event->data->object->id;

    User::where('stripe_customer_id', $customerId)
        ->update(['stripe_customer_id' => null]);
}
```

### Subscription Events

```php
use EncoreDigitalGroup\Stripe\Enums\SubscriptionStatus;

// customer.subscription.created - New subscription was created
protected function handleSubscriptionCreated(Event $event): void
{
    $subscription = StripeSubscription::fromStripeObject($event->data->object);

    Subscription::create([
        'user_id' => User::where('stripe_customer_id', $subscription->customer)->value('id'),
        'stripe_subscription_id' => $subscription->id,
        'status' => $subscription->status->value,
        'current_period_start' => $subscription->currentPeriodStart,
        'current_period_end' => $subscription->currentPeriodEnd
    ]);
}

// customer.subscription.updated - Subscription was modified
protected function handleSubscriptionUpdated(Event $event): void
{
    $subscription = StripeSubscription::fromStripeObject($event->data->object);

    $dbSubscription = Subscription::where('stripe_subscription_id', $subscription->id)->first();

    if (!$dbSubscription) {
        return;
    }

    $dbSubscription->update([
        'status' => $subscription->status->value,
        'current_period_start' => $subscription->currentPeriodStart,
        'current_period_end' => $subscription->currentPeriodEnd
    ]);

    // Handle status changes
    if ($subscription->status === SubscriptionStatus::Active) {
        // Grant access
        $dbSubscription->user->update(['has_active_subscription' => true]);
    } elseif (in_array($subscription->status, [SubscriptionStatus::Canceled, SubscriptionStatus::Unpaid])) {
        // Revoke access
        $dbSubscription->user->update(['has_active_subscription' => false]);
    }
}

// customer.subscription.deleted - Subscription was canceled/ended
protected function handleSubscriptionDeleted(Event $event): void
{
    $subscriptionId = $event->data->object->id;

    $subscription = Subscription::where('stripe_subscription_id', $subscriptionId)->first();

    if ($subscription) {
        $subscription->update(['status' => 'canceled', 'ended_at' => now()]);
        $subscription->user->update(['has_active_subscription' => false]);
    }
}
```

### Invoice Events

```php
// invoice.paid - Invoice was successfully paid
protected function handleInvoicePaid(Event $event): void
{
    $invoice = $event->data->object;

    // Record successful payment
    Payment::create([
        'user_id' => User::where('stripe_customer_id', $invoice->customer)->value('id'),
        'stripe_invoice_id' => $invoice->id,
        'amount' => $invoice->amount_paid / 100, // Convert from cents
        'currency' => $invoice->currency,
        'status' => 'paid',
        'paid_at' => now()
    ]);

    // Send receipt email
    $user = User::where('stripe_customer_id', $invoice->customer)->first();
    if ($user) {
        Mail::to($user)->send(new InvoicePaidMail($invoice));
    }
}

// invoice.payment_failed - Invoice payment attempt failed
protected function handleInvoicePaymentFailed(Event $event): void
{
    $invoice = $event->data->object;

    $user = User::where('stripe_customer_id', $invoice->customer)->first();

    if ($user) {
        // Notify user of failed payment
        Mail::to($user)->send(new PaymentFailedMail($invoice));

        // Log the failure
        PaymentFailure::create([
            'user_id' => $user->id,
            'stripe_invoice_id' => $invoice->id,
            'amount' => $invoice->amount_due / 100,
            'reason' => $invoice->last_payment_error?->message ?? 'Unknown'
        ]);
    }
}
```

### Payment Intent Events

```php
// payment_intent.succeeded - Payment was successful
protected function handlePaymentIntentSucceeded(Event $event): void
{
    $paymentIntent = $event->data->object;

    // Record the payment
    Payment::create([
        'stripe_payment_intent_id' => $paymentIntent->id,
        'amount' => $paymentIntent->amount / 100,
        'currency' => $paymentIntent->currency,
        'status' => 'succeeded'
    ]);
}

// payment_intent.payment_failed - Payment attempt failed
protected function handlePaymentIntentFailed(Event $event): void
{
    $paymentIntent = $event->data->object;

    logger()->warning('Payment intent failed', [
        'payment_intent_id' => $paymentIntent->id,
        'error' => $paymentIntent->last_payment_error?->message
    ]);
}
```

## Testing Webhooks

### Testing with StripeWebhook::fromRequest()

```php
use EncoreDigitalGroup\Stripe\Objects\Support\StripeWebhook;

test('can verify webhook signature and construct event', function () {
    $payload = json_encode([
        'id' => 'evt_test',
        'type' => 'customer.created',
        'data' => [
            'object' => [
                'id' => 'cus_test',
                'email' => 'test@example.com'
            ]
        ]
    ]);

    $secret = 'whsec_test';

    // Generate a valid signature
    $timestamp = time();
    $signedPayload = $timestamp . '.' . $payload;
    $signature = hash_hmac('sha256', $signedPayload, $secret);
    $header = "t={$timestamp},v1={$signature}";

    // Verify and construct event
    $event = StripeWebhook::fromRequest($payload, $header, $secret);

    expect($event)
        ->toBeInstanceOf(\Stripe\Event::class)
        ->and($event->type)->toBe('customer.created')
        ->and($event->data->object->id)->toBe('cus_test');
});
```

### Testing Webhook Controller

```php
use Illuminate\Support\Facades\Event;

test('webhook controller processes customer.created event', function () {
    // Create a valid webhook payload
    $payload = [
        'id' => 'evt_test',
        'type' => 'customer.created',
        'data' => [
            'object' => [
                'id' => 'cus_test123',
                'email' => 'test@example.com',
                'name' => 'Test User',
                'object' => 'customer'
            ]
        ]
    ];

    $secret = config('services.stripe.webhook_secret');
    $timestamp = time();
    $jsonPayload = json_encode($payload);
    $signedPayload = $timestamp . '.' . $jsonPayload;
    $signature = hash_hmac('sha256', $signedPayload, $secret);
    $header = "t={$timestamp},v1={$signature}";

    // Send webhook request
    $response = $this->post(route('stripe.webhook'), $payload, [
        'stripe-signature' => $header
    ]);

    $response->assertStatus(200);

    // Verify user was created
    $this->assertDatabaseHas('users', [
        'stripe_customer_id' => 'cus_test123',
        'email' => 'test@example.com'
    ]);
});
```

### Using Stripe CLI for Local Testing

The Stripe CLI can forward webhook events to your local development environment:

```bash
# Install Stripe CLI
brew install stripe/stripe-brew/stripe

# Login to Stripe
stripe login

# Forward webhooks to your local endpoint
stripe listen --forward-to localhost:8000/webhooks/stripe

# Trigger test events
stripe trigger customer.created
stripe trigger invoice.paid
stripe trigger payment_intent.succeeded
```

## Common Patterns

### Webhook Handler Service

```php
namespace App\Services;

use EncoreDigitalGroup\Stripe\Objects\Customer\StripeCustomer;
use EncoreDigitalGroup\Stripe\Objects\Subscription\StripeSubscription;
use Stripe\Event;

class StripeWebhookHandler
{
    public function handle(Event $event): void
    {
        logger()->info('Processing Stripe webhook', [
            'event_id' => $event->id,
            'event_type' => $event->type
        ]);

        match ($event->type) {
            // Customer events
            'customer.created' => $this->handleCustomerCreated($event),
            'customer.updated' => $this->handleCustomerUpdated($event),
            'customer.deleted' => $this->handleCustomerDeleted($event),

            // Subscription events
            'customer.subscription.created' => $this->handleSubscriptionCreated($event),
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($event),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event),

            // Invoice events
            'invoice.paid' => $this->handleInvoicePaid($event),
            'invoice.payment_failed' => $this->handleInvoicePaymentFailed($event),
            'invoice.upcoming' => $this->handleInvoiceUpcoming($event),

            // Payment events
            'payment_intent.succeeded' => $this->handlePaymentSucceeded($event),
            'payment_intent.payment_failed' => $this->handlePaymentFailed($event),

            // Default
            default => logger()->info('Unhandled webhook event', ['type' => $event->type])
        };
    }

    protected function handleCustomerCreated(Event $event): void
    {
        $customer = StripeCustomer::fromStripeObject($event->data->object);

        User::updateOrCreate(
            ['stripe_customer_id' => $customer->id],
            [
                'email' => $customer->email,
                'name' => $customer->name,
                'stripe_customer_created_at' => now()
            ]
        );
    }

    protected function handleSubscriptionUpdated(Event $event): void
    {
        $subscription = StripeSubscription::fromStripeObject($event->data->object);

        $dbSubscription = Subscription::firstOrCreate(
            ['stripe_subscription_id' => $subscription->id],
            [
                'user_id' => User::where('stripe_customer_id', $subscription->customer)->value('id')
            ]
        );

        $dbSubscription->update([
            'status' => $subscription->status->value,
            'current_period_start' => $subscription->currentPeriodStart,
            'current_period_end' => $subscription->currentPeriodEnd,
            'cancel_at' => $subscription->cancelAt,
            'canceled_at' => $subscription->canceledAt
        ]);

        // Sync user access based on subscription status
        $this->syncUserAccess($dbSubscription);
    }

    protected function syncUserAccess(Subscription $subscription): void
    {
        $hasAccess = in_array($subscription->status, ['active', 'trialing']);

        $subscription->user->update([
            'has_active_subscription' => $hasAccess,
            'subscription_updated_at' => now()
        ]);
    }
}
```

### Queued Webhook Processing

For reliability, process webhooks in queued jobs:

```php
// Controller
class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        try {
            $signature = StripeWebhook::getWebhookSignatureHeader();

            $event = StripeWebhook::fromRequest(
                $request->getContent(),
                $signature,
                config('services.stripe.webhook_secret')
            );

            // Queue the event for processing
            ProcessStripeWebhook::dispatch($event->id, $event->type, $event->data->object);

            return response()->json(['status' => 'queued']);

        } catch (\Exception $e) {
            logger()->error('Webhook verification failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Verification failed'], 400);
        }
    }
}

// Job
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\StripeWebhookHandler;

class ProcessStripeWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $eventId,
        public string $eventType,
        public object $eventData
    ) {}

    public function handle(StripeWebhookHandler $handler): void
    {
        // Reconstruct event object
        $event = new \Stripe\Event();
        $event->id = $this->eventId;
        $event->type = $this->eventType;
        $event->data = new \Stripe\StripeObject();
        $event->data->object = $this->eventData;

        $handler->handle($event);
    }
}
```

### Idempotent Webhook Processing

Stripe may send the same webhook multiple times. Handle this with idempotency:

```php
class StripeWebhookHandler
{
    public function handle(Event $event): void
    {
        // Check if we've already processed this event
        if (ProcessedWebhook::where('stripe_event_id', $event->id)->exists()) {
            logger()->info('Webhook already processed', ['event_id' => $event->id]);
            return;
        }

        // Process the event
        $this->processEvent($event);

        // Mark as processed
        ProcessedWebhook::create([
            'stripe_event_id' => $event->id,
            'event_type' => $event->type,
            'processed_at' => now()
        ]);
    }
}
```

## Next Steps

Now that you understand webhooks, explore the final documentation:

- **[Builders Reference](10-builders-reference.md)** - Comprehensive guide to the builder pattern

Or revisit core concepts:

- **[Customers](02-customers.md)** - Customer management
- **[Subscriptions](07-subscriptions.md)** - Subscription lifecycle
- **[Financial Connections](08-financial-connections.md)** - Bank account linking
- **[Testing](05-testing.md)** - Testing strategies
