# Installation

## Requirements

- PHP 8.3 or higher
- Laravel 11.x or 12.x
- Stripe PHP SDK v16.5+ or v17.0+
- Composer

## Install via Composer

```bash
composer require encoredigitalgroup/common-stripe
```

The package uses Laravel's auto-discovery feature, so the service provider will be registered automatically.

## Configuration

### Environment Variables

Add your Stripe API keys to your `.env` file:

```env
STRIPE_SECRET_KEY=sk_test_...
STRIPE_PUBLISHABLE_KEY=pk_test_...
```

### Configuration File

The library uses a configuration approach that reads from your environment. The secret key is required for API operations.

```php
use EncoreDigitalGroup\Common\Stripe\Stripe;

// The library automatically reads STRIPE_SECRET_KEY from your environment
// No additional configuration needed
```

### Multiple Environments

For different environments (development, staging, production), simply use different Stripe keys in each environment's `.env` file:

```env
# Development
STRIPE_SECRET_KEY=sk_test_...

# Production
STRIPE_SECRET_KEY=sk_live_...
```

## Next Steps

- [Quick Start Guide](quick-start.md) - Start building with Stripe
- [Architecture Overview](architecture.md) - Understand how the library works
- [Testing Guide](testing.md) - Learn how to test Stripe integrations
