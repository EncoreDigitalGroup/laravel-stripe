# Eloquent versus DTOs

Stripe API data is represented by package DTOs, not Eloquent models.

**Rules:**
- Keep DTOs under `src/php/Objects/` and services under `src/php/Services/`.
- Convert Stripe SDK objects with `fromStripeObject()` and send requests with `toArray()`.
- Do not add Eloquent models or host-application persistence semantics to the package.

**Example:** Use `StripePrice::fromStripeObject($price)` at the SDK boundary.

> Severity for plan review: **WARN**.
