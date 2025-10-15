# Laravel Stripe

A clean, type-safe PHP interface for the Stripe API designed specifically for Laravel applications. This library wraps the Stripe PHP SDK with strongly-typed objects,
service classes, and enums while maintaining full compatibility with Laravel 11-12.

## Why This Library?

The official Stripe PHP SDK, while powerful, returns dynamic objects and arrays that lack type safety and IDE support. This library bridges that gap by providing:

- **Type Safety**: All Stripe objects are represented as strongly-typed PHP classes with full PHPStan Level 8 compliance
- **Laravel Integration**: Seamless integration with Laravel's service container and testing infrastructure
- **Developer Experience**: Rich IDE autocompletion, type hints, and inline documentation
- **Consistent API**: Clean, predictable methods that follow Laravel conventions
- **Comprehensive Testing**: Built-in testing utilities that fake Stripe API calls without network requests

## Quick Start

Install via Composer:

```bash
composer require encoredigitalgroup/stripe
```

Configure your Stripe secret key in `.env`:

```env
STRIPE_SECRET_KEY=sk_test_...
```

Start using the library:

```php
use EncoreDigitalGroup\Stripe\Stripe;

// Create a customer
$customer = Stripe::customers()->create(Stripe::customer(
    email: 'customer@example.com',
    name: 'John Doe'
));

echo $customer->id; // cus_...
```

## Core Features

### Services

Clean service methods accessible via the Stripe facade:

- `Stripe::customers()` - Create, update, retrieve, delete, list, and search customers
- `Stripe::products()` - Manage products with archive/reactivate functionality
- `Stripe::prices()` - Handle pricing with support for recurring billing, tiers, and complex configurations
- `Stripe::subscriptions()` - Full subscription lifecycle management

### Type-Safe Objects

Immutable objects created via factory methods:

- `Stripe::customer()` - Customer objects with address and shipping support
- `Stripe::product()` - Product objects with metadata, images, and package dimensions
- `Stripe::price()` - Price objects with complex recurring billing, tiers, and custom unit amounts
- `Stripe::address()` - Address objects for billing and shipping

### Enums

String-backed enums for Stripe constants:

- `RecurringInterval` (Month, Year, Day, Week)
- `PriceType` (OneTime, Recurring)
- `SubscriptionStatus` (Active, Canceled, Incomplete, etc.)
- And many more for type-safe API interactions

### Testing Infrastructure

Comprehensive testing utilities:

- `Stripe::fake()` for mocking API calls
- `StripeFixtures` for realistic test data
- Custom PHPUnit expectations for asserting API calls
- No network requests in tests

## Requirements

- PHP 8.3+
- Laravel 11 or 12
- Stripe PHP SDK ^18.0

## Documentation

**[=� Read the Full Documentation �](docs/)**

The documentation is organized as a coherent story that will take you from basic concepts to advanced usage:

1. **[Getting Started](docs/01-getting-started.md)** - Installation, configuration, and basic concepts
2. **[Customers](docs/02-customers.md)** - Everything about customer management
3. **[Products](docs/03-products.md)** - Product creation, management, and lifecycle
4. **[Prices](docs/04-prices.md)** - Complex pricing, recurring billing, and tiers
5. **[Testing](docs/05-testing.md)** - Comprehensive testing strategies and utilities
6. **[Architecture](docs/06-architecture.md)** - Deep dive into library design and patterns

## Example: Complete E-commerce Flow

```php
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Enums\{PriceType, RecurringInterval};

// 1. Create a customer
$customer = Stripe::customers()->create(Stripe::customer(
    email: 'customer@example.com',
    name: 'John Doe'
));

// 2. Create a product
$product = Stripe::products()->create(Stripe::product(
    name: 'Premium Subscription',
    description: 'Monthly premium features'
));

// 3. Create a recurring price
$price = Stripe::prices()->create(Stripe::price(
    product: $product->id,
    currency: 'usd',
    unitAmount: 2999, // $29.99
    type: PriceType::Recurring,
    recurring: [
        'interval' => RecurringInterval::Month,
        'interval_count' => 1
    ]
));

echo "Created customer {$customer->id} with product {$product->id} priced at {$price->id}";
```

## Contributing

Contributions to this repository are governed by the Encore Digital Group [Contribution Terms](https://docs.encoredigitalgroup.com/Contributing/Terms/).
Additional details on how to contribute are available [here](https://docs.encoredigitalgroup.com/Contributing/).

## License

This repository is licensed using a modified version of the BSD 3-Clause License.
The license is available for review [here](https://docs.encoredigitalgroup.com/LicenseTerms/).
