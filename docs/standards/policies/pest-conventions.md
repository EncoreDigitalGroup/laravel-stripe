# Pest conventions

Package tests use Pest 4 and the shared Stripe fake infrastructure.

**Rules:**
- Run `./vendor/bin/pest --parallel`; scope work with a test file or directory when useful.
- Use `tests/Feature/` for service integration behavior and `tests/Unit/` for object and support behavior.
- Use `StripeFixtures`, `StripeMethod`, and `Stripe::fake()` instead of raw Stripe payloads.

**Example:** Assert `toHaveCalledStripeMethod(StripeMethod::CustomersCreate)` after a service call.

> Severity for plan review: **BLOCK**.
