# Common Stripe Documentation

A Laravel library providing a clean, type-safe PHP interface for the Stripe API. This library wraps the Stripe PHP SDK with strongly-typed objects, service classes, and
enums while maintaining compatibility with Laravel 11-12 applications.

## Documentation Structure

### Getting Started

- [Installation](installation.md) - Setup and configuration
- [Quick Start](quick-start.md) - Your first integration

### Core Concepts

- [Architecture Overview](architecture.md) - How the library is structured
- [Type Safety](type-safety.md) - Understanding DTOs and enums
- [Service Pattern](services.md) - Working with Stripe services

### Testing

- [Testing Guide](testing.md) - Using Stripe::fake() for tests
- [Test Fixtures](fixtures.md) - Creating realistic test data

### API Reference

- [Stripe Facade](api/stripe-facade.md) - Main entry point
- [Customers](api/customers.md) - Customer management
- [Products & Prices](api/products-prices.md) - Product catalog
- [Subscriptions](api/subscriptions.md) - Subscription management

### Advanced Topics

- [Error Handling](advanced/error-handling.md) - Dealing with failures
- [Laravel Integration](advanced/laravel-integration.md) - Container bindings and service providers
- [Migration Guide](advanced/migration-guide.md) - Moving from Stripe SDK

## Quick Example

```php
use EncoreDigitalGroup\Common\Stripe\Stripe;

// Create a customer
$customer = Stripe::customers()->create(
    Stripe::customer(
        email: 'customer@example.com',
        name: 'John Doe'
    )
);

// Create a product with price
$product = Stripe::products()->create(
    Stripe::product(name: 'Premium Plan')
);

$price = Stripe::prices()->create(
    Stripe::price(
        product: $product->id,
        unitAmount: 2000,
        currency: 'usd'
    )
);
```

## Support

- **GitHub Issues**: [Report bugs and request features](https://github.com/encoredigitalgroup/common-stripe/issues)
- **PHP Version**: 8.3+
- **Laravel Version**: 11.x - 12.x
- **Stripe API**: Compatible with PHP SDK v16.5+ and v17.0+
