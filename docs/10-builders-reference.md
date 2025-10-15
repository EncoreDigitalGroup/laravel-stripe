# Builders Reference

The Laravel Stripe library provides a fluent builder pattern for creating Stripe data objects. This comprehensive guide covers all available builders, when to use them, and practical examples for each.

## Table of Contents

- [Understanding the Builder Pattern](#understanding-the-builder-pattern)
- [Three Ways to Create Objects](#three-ways-to-create-objects)
- [Main Entity Builders](#main-entity-builders)
- [Support Object Builders](#support-object-builders)
- [Sub-Object Builders](#sub-object-builders)
- [Builder Method Reference](#builder-method-reference)
- [Practical Examples](#practical-examples)
- [When to Use Each Method](#when-to-use-each-method)

## Understanding the Builder Pattern

The builder pattern provides a fluent, discoverable way to create complex objects. The library implements three access patterns—all functionally equivalent—to suit different coding styles.

### Why Use Builders?

```php
// ❌ Without builders - verbose, error-prone
use EncoreDigitalGroup\Stripe\Objects\Customer\StripeCustomer;
use EncoreDigitalGroup\Stripe\Objects\Support\StripeAddress;

$address = new StripeAddress(
    line1: '123 Main St',
    line2: null,
    city: 'San Francisco',
    state: 'CA',
    postalCode: '94105',
    country: 'US'
);

$customer = new StripeCustomer(
    id: null,
    email: 'john@example.com',
    name: 'John Doe',
    description: null,
    phone: null,
    address: $address,
    shipping: null,
    // ... many null parameters
);

// ✅ With builders - clean, fluent, discoverable
use EncoreDigitalGroup\Stripe\Stripe;

$customer = Stripe::customer(
    email: 'john@example.com',
    name: 'John Doe',
    address: Stripe::address(
        line1: '123 Main St',
        city: 'San Francisco',
        state: 'CA',
        postalCode: '94105',
        country: 'US'
    )
);
```

### Benefits of the Builder Pattern

1. **IDE Autocomplete** - Discover available methods and parameters
2. **Skip Null Values** - Only specify values you need
3. **Type Safety** - Catch errors at compile time
4. **Nested Objects** - Clean syntax for complex structures
5. **Discoverability** - Easy to explore the API

## Three Ways to Create Objects

All three methods produce identical results. Choose based on your preference and use case.

### Method 1: Direct DTO Creation

Use `::make()` directly on the DTO class:

```php
use EncoreDigitalGroup\Stripe\Objects\Customer\StripeCustomer;

$customer = StripeCustomer::make(
    email: 'john@example.com',
    name: 'John Doe'
);
```

**When to use:**
- You know exactly which class you need
- Writing quick, concise code
- No need for IDE discovery

### Method 2: Full Builder Pattern

Use `Stripe::builder()` for maximum discoverability:

```php
use EncoreDigitalGroup\Stripe\Stripe;

$customer = Stripe::builder()->customer()->build(
    email: 'john@example.com',
    name: 'John Doe'
);
```

**When to use:**
- Learning the library
- Complex nested objects
- You want IDE autocomplete to guide you
- Team prefers explicit builder syntax

### Method 3: Facade Shortcuts (Recommended)

Use `Stripe::factoryMethod()` for the best balance:

```php
use EncoreDigitalGroup\Stripe\Stripe;

$customer = Stripe::customer(
    email: 'john@example.com',
    name: 'John Doe'
);
```

**When to use:**
- Most situations (recommended default)
- Clean, concise syntax
- Good IDE support
- Under the hood, it uses the builder pattern

## Main Entity Builders

These builders create the primary Stripe resources you'll work with.

### customer()

Creates `StripeCustomer` objects for customer management.

```php
use EncoreDigitalGroup\Stripe\Stripe;

// Simple customer
$customer = Stripe::customer(
    email: 'customer@example.com',
    name: 'Jane Smith'
);

// Customer with full details
$customer = Stripe::customer(
    email: 'customer@example.com',
    name: 'Jane Smith',
    description: 'Premium customer',
    phone: '+1-555-123-4567',
    address: Stripe::address(
        line1: '123 Business Ave',
        city: 'New York',
        state: 'NY',
        postalCode: '10001',
        country: 'US'
    ),
    shipping: Stripe::shipping(
        name: 'Jane Smith',
        address: Stripe::address(
            line1: '456 Home St',
            city: 'Brooklyn',
            state: 'NY',
            postalCode: '11201',
            country: 'US'
        ),
        phone: '+1-555-987-6543'
    ),
    metadata: [
        'internal_id' => 'CUST-12345',
        'account_manager' => 'John Doe'
    ]
);

// All three methods work the same:
$customer = StripeCustomer::make(email: '...');                         // Method 1
$customer = Stripe::builder()->customer()->build(email: '...');         // Method 2
$customer = Stripe::customer(email: '...');                             // Method 3 (recommended)
```

**Customer Properties:**
- `id` - Customer ID (read-only on create)
- `email` - Customer email address
- `name` - Customer full name
- `description` - Internal description
- `phone` - Phone number
- `address` - Billing address (StripeAddress)
- `shipping` - Shipping information (StripeShipping)
- `metadata` - Custom key-value data

### product()

Creates `StripeProduct` objects for products and services.

```php
use EncoreDigitalGroup\Stripe\Stripe;

// Simple product
$product = Stripe::product(
    name: 'Premium Subscription',
    description: 'Access to all premium features'
);

// Product with rich metadata
$product = Stripe::product(
    name: 'Enterprise License',
    description: 'Full enterprise access with priority support',
    active: true,
    images: [
        'https://example.com/images/product-1.jpg',
        'https://example.com/images/product-2.jpg'
    ],
    metadata: [
        'category' => 'software',
        'tier' => 'enterprise',
        'support_level' => 'premium'
    ],
    url: 'https://example.com/products/enterprise',
    shippable: false,
    taxCode: 'txcd_10000000' // Software - downloaded
);

// Physical product with shipping
$product = Stripe::product(
    name: 'Hardware Device',
    shippable: true,
    packageDimensions: [
        'height' => 5.0,
        'length' => 10.0,
        'width' => 8.0,
        'weight' => 2.5
    ],
    unitLabel: 'device'
);
```

**Product Properties:**
- `id` - Product ID (read-only on create)
- `name` - Product name (required)
- `description` - Product description
- `active` - Whether product is active
- `images` - Array of image URLs
- `metadata` - Custom key-value data
- `defaultPrice` - Default price ID
- `taxCode` - Tax code for calculations
- `unitLabel` - Unit of measurement
- `url` - Product URL
- `shippable` - Whether requires shipping
- `packageDimensions` - Shipping dimensions
- `created` - Creation timestamp (read-only)
- `updated` - Last update timestamp (read-only)

### price()

Creates `StripePrice` objects for pricing configurations.

```php
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Enums\{PriceType, RecurringInterval, TierMode, BillingScheme};

// Simple one-time price
$price = Stripe::price(
    product: 'prod_abc123',
    currency: 'usd',
    unitAmount: 1999  // $19.99
);

// Recurring subscription price
$price = Stripe::price(
    product: 'prod_abc123',
    currency: 'usd',
    unitAmount: 2999,  // $29.99
    type: PriceType::Recurring,
    recurring: Stripe::recurring(
        interval: RecurringInterval::Month,
        intervalCount: 1
    )
);

// Tiered pricing
$price = Stripe::price(
    product: 'prod_abc123',
    currency: 'usd',
    billingScheme: BillingScheme::Tiered,
    tiers: [
        Stripe::tier(upTo: 10, unitAmount: 1000),      // First 10: $10 each
        Stripe::tier(upTo: 50, unitAmount: 800),       // Next 40: $8 each
        Stripe::tier(upTo: null, unitAmount: 600)      // 51+: $6 each
    ],
    tiersMode: TierMode::Volume,
    type: PriceType::Recurring,
    recurring: Stripe::recurring(interval: RecurringInterval::Month)
);

// Usage-based pricing
$price = Stripe::price(
    product: 'prod_abc123',
    currency: 'usd',
    billingScheme: BillingScheme::PerUnit,
    unitAmount: 50,  // $0.50 per unit
    type: PriceType::Recurring,
    recurring: Stripe::recurring(
        interval: RecurringInterval::Month,
        usageType: 'metered'
    )
);

// Customer-defined pricing
$price = Stripe::price(
    product: 'prod_abc123',
    currency: 'usd',
    customUnitAmount: Stripe::customUnitAmount(
        minimum: 500,    // Minimum $5.00
        maximum: 100000, // Maximum $1,000.00
        preset: 2000     // Default $20.00
    )
);
```

**Price Properties:**
- `id` - Price ID (read-only on create)
- `product` - Product ID
- `currency` - Three-letter currency code
- `unitAmount` - Amount in cents
- `type` - Price type (one_time, recurring)
- `active` - Whether price is active
- `recurring` - Recurring details (StripeRecurring)
- `billingScheme` - Billing scheme (per_unit, tiered)
- `tiers` - Pricing tiers array
- `tiersMode` - Tier calculation mode
- `customUnitAmount` - Customer-defined pricing config
- `metadata` - Custom key-value data

### subscription()

Creates `StripeSubscription` objects for subscription management.

```php
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Enums\{CollectionMethod, ProrationBehavior};
use Carbon\Carbon;

// Simple subscription
$subscription = Stripe::subscription(
    customer: 'cus_abc123',
    items: [
        ['price' => 'price_monthly']
    ]
);

// Subscription with trial
$subscription = Stripe::subscription(
    customer: 'cus_abc123',
    items: [
        ['price' => 'price_monthly']
    ],
    trialEnd: Carbon::now()->addDays(14)
);

// Complex subscription with multiple items
$subscription = Stripe::subscription(
    customer: 'cus_abc123',
    items: [
        ['price' => 'price_base', 'quantity' => 1],
        ['price' => 'price_users', 'quantity' => 5],
        ['price' => 'price_storage', 'quantity' => 100]
    ],
    defaultPaymentMethod: 'pm_abc123',
    collectionMethod: CollectionMethod::ChargeAutomatically,
    prorationBehavior: ProrationBehavior::CreateProrations,
    metadata: [
        'plan_name' => 'Enterprise',
        'account_id' => 'ACC-12345'
    ]
);

// Subscription with custom billing anchor
$subscription = Stripe::subscription(
    customer: 'cus_abc123',
    items: [['price' => 'price_monthly']],
    billingCycleAnchorConfig: StripeBillingCycleAnchorConfig::make(
        dayOfMonth: 1,
        hour: 0,
        minute: 0,
        second: 0
    )
);
```

**Subscription Properties:**
- `id` - Subscription ID (read-only on create)
- `customer` - Customer ID
- `status` - Subscription status enum
- `currentPeriodStart` - Current period start (CarbonImmutable)
- `currentPeriodEnd` - Current period end (CarbonImmutable)
- `cancelAt` - Scheduled cancellation (CarbonImmutable)
- `canceledAt` - Actual cancellation (CarbonImmutable)
- `trialStart` - Trial start (CarbonImmutable)
- `trialEnd` - Trial end (CarbonImmutable)
- `items` - Subscription items array
- `defaultPaymentMethod` - Payment method ID
- `metadata` - Custom key-value data
- `currency` - Currency code
- `collectionMethod` - Collection method enum
- `billingCycleAnchorConfig` - Billing anchor config
- `prorationBehavior` - Proration behavior enum
- `cancelAtPeriodEnd` - Cancel flag
- `daysUntilDue` - Days until invoice due
- `description` - Subscription description

### financialConnection()

Creates `StripeFinancialConnection` objects for bank account linking.

```php
use EncoreDigitalGroup\Stripe\Stripe;

// Basic financial connection
$connection = Stripe::financialConnection(
    customer: Stripe::customer(id: 'cus_abc123'),
    permissions: ['payment_method']
);

// Connection with multiple permissions
$connection = Stripe::financialConnection(
    customer: Stripe::customer(id: 'cus_abc123'),
    permissions: [
        'transactions',
        'balances',
        'ownership',
        'payment_method'
    ]
);
```

**FinancialConnection Properties:**
- `customer` - StripeCustomer object (required)
- `permissions` - Array of permission strings (default: ['transactions'])

**Available Permissions:**
- `transactions` - Access transaction history
- `balances` - Access balance information
- `ownership` - Access ownership details
- `payment_method` - Use for payments

## Support Object Builders

These builders create supporting objects used within main entities.

### address()

Creates `StripeAddress` objects for billing and shipping addresses.

```php
use EncoreDigitalGroup\Stripe\Stripe;

// Complete address
$address = Stripe::address(
    line1: '123 Main Street',
    line2: 'Suite 400',
    city: 'San Francisco',
    state: 'CA',
    postalCode: '94105',
    country: 'US'
);

// Minimal address
$address = Stripe::address(
    line1: '456 Oak Ave',
    city: 'Portland',
    state: 'OR',
    postalCode: '97201',
    country: 'US'
);

// International address
$address = Stripe::address(
    line1: '10 Downing Street',
    city: 'London',
    postalCode: 'SW1A 2AA',
    country: 'GB'
);
```

**Address Properties:**
- `line1` - Street address line 1
- `line2` - Street address line 2 (optional)
- `city` - City
- `state` - State/province (optional for some countries)
- `postalCode` - ZIP or postal code
- `country` - Two-letter country code (ISO 3166-1 alpha-2)

### shipping()

Creates `StripeShipping` objects for shipping information.

```php
use EncoreDigitalGroup\Stripe\Stripe;

// Complete shipping information
$shipping = Stripe::shipping(
    name: 'Jane Doe',
    phone: '+1-555-123-4567',
    address: Stripe::address(
        line1: '789 Shipping Lane',
        line2: 'Apt 2B',
        city: 'Seattle',
        state: 'WA',
        postalCode: '98101',
        country: 'US'
    )
);

// Minimal shipping
$shipping = Stripe::shipping(
    name: 'John Smith',
    address: Stripe::address(
        line1: '321 Delivery St',
        city: 'Austin',
        state: 'TX',
        postalCode: '78701',
        country: 'US'
    )
);
```

**Shipping Properties:**
- `name` - Recipient name
- `phone` - Contact phone (optional)
- `address` - Shipping address (StripeAddress)

### webhook()

Creates `StripeWebhook` objects for webhook configuration.

```php
use EncoreDigitalGroup\Stripe\Stripe;

// Basic webhook
$webhook = Stripe::webhook(
    url: 'https://myapp.com/webhooks/stripe',
    events: ['customer.created', 'customer.updated']
);

// Comprehensive webhook
$webhook = Stripe::webhook(
    url: 'https://myapp.com/webhooks/stripe',
    events: [
        // Customer events
        'customer.created',
        'customer.updated',
        'customer.deleted',

        // Subscription events
        'customer.subscription.created',
        'customer.subscription.updated',
        'customer.subscription.deleted',

        // Invoice events
        'invoice.paid',
        'invoice.payment_failed',
        'invoice.upcoming',

        // Payment events
        'payment_intent.succeeded',
        'payment_intent.payment_failed'
    ]
);
```

**Webhook Properties:**
- `url` - Webhook endpoint URL
- `events` - Array of event types to subscribe to

## Sub-Object Builders

These builders create specialized objects used within pricing and other complex structures.

### recurring()

Creates `StripeRecurring` objects for recurring billing configuration.

```php
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Enums\{RecurringInterval, AggregateUsage};

// Monthly billing
$recurring = Stripe::recurring(
    interval: RecurringInterval::Month,
    intervalCount: 1
);

// Quarterly billing
$recurring = Stripe::recurring(
    interval: RecurringInterval::Month,
    intervalCount: 3
);

// Annual billing
$recurring = Stripe::recurring(
    interval: RecurringInterval::Year,
    intervalCount: 1
);

// Metered usage billing
$recurring = Stripe::recurring(
    interval: RecurringInterval::Month,
    usageType: 'metered',
    aggregateUsage: AggregateUsage::Sum
);
```

**Recurring Properties:**
- `interval` - Billing interval enum (day, week, month, year)
- `intervalCount` - Number of intervals between billings
- `usageType` - Usage type ('metered' or 'licensed')
- `aggregateUsage` - How to aggregate usage (sum, last_during_period, max)

### tier()

Creates pricing tier objects for tiered pricing models.

```php
use EncoreDigitalGroup\Stripe\Stripe;

// Volume tiers
$tiers = [
    Stripe::tier(upTo: 10, unitAmount: 1000),     // 1-10: $10 each
    Stripe::tier(upTo: 50, unitAmount: 800),      // 11-50: $8 each
    Stripe::tier(upTo: null, unitAmount: 600)     // 51+: $6 each
];

// Flat fee + per unit
$tiers = [
    Stripe::tier(upTo: 1, flatAmount: 5000),      // Base: $50
    Stripe::tier(upTo: null, unitAmount: 100)     // Each additional: $1
];

// Graduated tiers with flat fees
$tiers = [
    Stripe::tier(upTo: 5, unitAmount: 2000, flatAmount: 0),
    Stripe::tier(upTo: 20, unitAmount: 1500, flatAmount: 10000),
    Stripe::tier(upTo: null, unitAmount: 1000, flatAmount: 0)
];
```

**Tier Properties:**
- `upTo` - Upper bound of tier (null for infinity)
- `unitAmount` - Per-unit cost in cents
- `flatAmount` - Flat fee for tier in cents
- `unitAmountDecimal` - Precise decimal amount

### customUnitAmount()

Creates custom unit amount objects for customer-defined pricing.

```php
use EncoreDigitalGroup\Stripe\Stripe;

// Pay-what-you-want with bounds
$customAmount = Stripe::customUnitAmount(
    minimum: 500,      // Minimum $5.00
    maximum: 100000,   // Maximum $1,000.00
    preset: 2000       // Suggested $20.00
);

// Donation with minimum
$customAmount = Stripe::customUnitAmount(
    minimum: 100,      // Minimum $1.00
    preset: 1000       // Suggested $10.00
    // No maximum
);
```

**CustomUnitAmount Properties:**
- `minimum` - Minimum amount in cents
- `maximum` - Maximum amount in cents (optional)
- `preset` - Suggested/default amount in cents

### bankAccount()

Creates `StripeBankAccount` objects for connected bank account data.

```php
use EncoreDigitalGroup\Stripe\Stripe;
use Carbon\CarbonImmutable;

// Basic bank account
$bankAccount = Stripe::bankAccount(
    id: 'fca_abc123',
    category: 'checking',
    displayName: 'Primary Checking',
    institutionName: 'Chase Bank',
    last4: '1234'
);

// Complete bank account with transaction refresh
$bankAccount = Stripe::bankAccount(
    id: 'fca_abc123',
    category: 'savings',
    created: CarbonImmutable::now(),
    displayName: 'Savings Account',
    institutionName: 'Wells Fargo',
    last4: '5678',
    liveMode: true,
    permissions: ['payment_method', 'transactions'],
    subscriptions: ['transactions'],
    supportedPaymentMethodTypes: ['us_bank_account'],
    transactionRefresh: Stripe::transactionRefresh(
        status: 'succeeded',
        lastAttemptedAt: time() - 3600,
        nextRefreshAvailableAt: time() + 82800
    )
);
```

**BankAccount Properties:**
- `id` - Financial Connection Account ID
- `category` - Account type (checking, savings, etc.)
- `created` - Creation timestamp (CarbonImmutable)
- `displayName` - User-friendly account name
- `institutionName` - Bank name
- `last4` - Last 4 digits
- `liveMode` - Whether in live mode
- `permissions` - Granted permissions array
- `subscriptions` - Active data subscriptions array
- `supportedPaymentMethodTypes` - Payment method types array
- `transactionRefresh` - Transaction refresh status

### transactionRefresh()

Creates `StripeTransactionRefresh` objects for transaction sync status.

```php
use EncoreDigitalGroup\Stripe\Stripe;

// Transaction refresh status
$refresh = Stripe::transactionRefresh(
    id: 'tr_abc123',
    status: 'succeeded',
    lastAttemptedAt: time() - 3600,
    nextRefreshAvailableAt: time() + 82800
);

// Pending refresh
$refresh = Stripe::transactionRefresh(
    status: 'pending'
);
```

**TransactionRefresh Properties:**
- `id` - Refresh ID
- `lastAttemptedAt` - Unix timestamp of last attempt
- `nextRefreshAvailableAt` - Unix timestamp of next availability
- `status` - Status (pending, succeeded, failed)

## Builder Method Reference

Quick reference for all builder access methods.

### Via Stripe::builder()

```php
use EncoreDigitalGroup\Stripe\Stripe;

$builder = Stripe::builder();

// Main entities
$builder->customer()->build(...);
$builder->product()->build(...);
$builder->price()->build(...);
$builder->subscription()->build(...);
$builder->financialConnection()->build(...);

// Support objects
$builder->address()->build(...);
$builder->shipping()->build(...);
$builder->webhook()->build(...);

// Sub-objects
$builder->recurring()->build(...);
$builder->tier()->build(...);
$builder->customUnitAmount()->build(...);
$builder->bankAccount()->build(...);
$builder->transactionRefresh()->build(...);
```

### Via Stripe Facade Shortcuts (Recommended)

```php
use EncoreDigitalGroup\Stripe\Stripe;

// Main entities
Stripe::customer(...);
Stripe::product(...);
Stripe::price(...);
Stripe::subscription(...);
Stripe::financialConnection(...);

// Support objects
Stripe::address(...);
Stripe::shipping(...);
Stripe::webhook(...);

// Sub-objects
Stripe::recurring(...);
// Note: tier() and customUnitAmount() are not exposed as facade shortcuts
// Use Stripe::builder()->tier() or direct DTO creation for these
```

## Practical Examples

Real-world scenarios demonstrating builder usage.

### E-commerce Checkout

```php
use EncoreDigitalGroup\Stripe\Stripe;

// Create customer with full address
$customer = Stripe::customers()->create(Stripe::customer(
    email: $request->email,
    name: $request->name,
    phone: $request->phone,
    address: Stripe::address(
        line1: $request->address_line1,
        line2: $request->address_line2,
        city: $request->city,
        state: $request->state,
        postalCode: $request->postal_code,
        country: $request->country
    ),
    shipping: Stripe::shipping(
        name: $request->shipping_name ?? $request->name,
        phone: $request->shipping_phone ?? $request->phone,
        address: Stripe::address(
            line1: $request->shipping_line1 ?? $request->address_line1,
            line2: $request->shipping_line2 ?? $request->address_line2,
            city: $request->shipping_city ?? $request->city,
            state: $request->shipping_state ?? $request->state,
            postalCode: $request->shipping_postal ?? $request->postal_code,
            country: $request->shipping_country ?? $request->country
        )
    ),
    metadata: [
        'order_id' => $order->id,
        'source' => 'web_checkout'
    ]
));
```

### SaaS Subscription with Trial

```php
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Enums\ProrationBehavior;
use Carbon\Carbon;

$subscription = Stripe::subscriptions()->create(Stripe::subscription(
    customer: $user->stripe_customer_id,
    items: [
        [
            'price' => config('stripe.plans.professional.price_id'),
            'quantity' => $team->user_count
        ]
    ],
    trialEnd: Carbon::now()->addDays(14),
    defaultPaymentMethod: $paymentMethodId,
    prorationBehavior: ProrationBehavior::CreateProrations,
    metadata: [
        'team_id' => $team->id,
        'plan' => 'professional',
        'trial_source' => 'sign_up_flow'
    ]
));
```

### Tiered Usage Pricing

```php
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Enums\{PriceType, RecurringInterval, BillingScheme, TierMode};

$product = Stripe::products()->create(Stripe::product(
    name: 'API Access',
    description: 'Tiered pricing based on API usage'
));

$price = Stripe::prices()->create(Stripe::price(
    product: $product->id,
    currency: 'usd',
    billingScheme: BillingScheme::Tiered,
    tiersMode: TierMode::Graduated,
    tiers: [
        Stripe::builder()->tier()->build(
            upTo: 1000,
            unitAmount: 10  // $0.10 per request
        ),
        Stripe::builder()->tier()->build(
            upTo: 10000,
            unitAmount: 5   // $0.05 per request
        ),
        Stripe::builder()->tier()->build(
            upTo: null,
            unitAmount: 2   // $0.02 per request
        )
    ],
    type: PriceType::Recurring,
    recurring: Stripe::recurring(
        interval: RecurringInterval::Month,
        usageType: 'metered'
    )
));
```

### Complete Product Catalog Setup

```php
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Enums\{PriceType, RecurringInterval};

// Create product
$product = Stripe::products()->create(Stripe::product(
    name: 'Premium Subscription',
    description: 'All premium features unlocked',
    active: true,
    images: [
        'https://example.com/images/premium-hero.jpg'
    ],
    metadata: [
        'category' => 'subscription',
        'tier' => 'premium',
        'features' => 'unlimited_storage,priority_support,advanced_analytics'
    ],
    url: 'https://example.com/plans/premium'
));

// Create monthly price
$monthlyPrice = Stripe::prices()->create(Stripe::price(
    product: $product->id,
    currency: 'usd',
    unitAmount: 2999,  // $29.99
    type: PriceType::Recurring,
    recurring: Stripe::recurring(
        interval: RecurringInterval::Month
    ),
    metadata: ['billing_period' => 'monthly']
));

// Create annual price (17% discount)
$annualPrice = Stripe::prices()->create(Stripe::price(
    product: $product->id,
    currency: 'usd',
    unitAmount: 29900,  // $299.00 (saves $59.88)
    type: PriceType::Recurring,
    recurring: Stripe::recurring(
        interval: RecurringInterval::Year
    ),
    metadata: ['billing_period' => 'annual', 'discount_percent' => '17']
));
```

## When to Use Each Method

### Use Direct DTO Creation (`::make()`) When:

- You're very familiar with the library
- Writing quick, one-off code
- The object is simple with few properties
- You want minimal syntax

```php
// Perfect for simple objects
$address = StripeAddress::make(
    line1: '123 Main St',
    city: 'Portland',
    state: 'OR',
    postalCode: '97201',
    country: 'US'
);
```

### Use Full Builder Pattern (`Stripe::builder()`) When:

- Learning the library and exploring available options
- You want maximum IDE autocompletion
- Building complex nested structures
- Teaching or documenting code
- Team prefers explicit builder syntax

```php
// Great for discovery and complex objects
$customer = Stripe::builder()->customer()->build(
    email: 'customer@example.com',
    address: Stripe::builder()->address()->build(
        line1: '123 Main St',
        city: 'Portland',
        state: 'OR',
        postalCode: '97201',
        country: 'US'
    )
);
```

### Use Facade Shortcuts (`Stripe::method()`) When:

- Most situations (recommended default)
- You want clean, readable code
- Good balance of brevity and discoverability
- IDE support is important
- You're comfortable with the library

```php
// Recommended for most use cases
$customer = Stripe::customer(
    email: 'customer@example.com',
    address: Stripe::address(
        line1: '123 Main St',
        city: 'Portland',
        state: 'OR',
        postalCode: '97201',
        country: 'US'
    )
);
```

## Next Steps

Now that you understand the builder pattern, explore how to use these objects:

- **[Customers](02-customers.md)** - Customer management
- **[Products](03-products.md)** - Product catalog management
- **[Prices](04-prices.md)** - Pricing configurations
- **[Subscriptions](07-subscriptions.md)** - Subscription lifecycle
- **[Financial Connections](08-financial-connections.md)** - Bank account linking
- **[Webhooks](09-webhooks.md)** - Event handling
- **[Testing](05-testing.md)** - Testing strategies
