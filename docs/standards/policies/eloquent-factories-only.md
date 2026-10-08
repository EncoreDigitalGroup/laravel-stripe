# Eloquent factories only

This package does not own Eloquent models or factories; host factories remain outside its boundary.

**Rules:**
- Do not add `database/factories/` or Eloquent factory dependencies to the package.
- Use `StripeFixtures` for package test data.
- Keep host-model setup in the host application that owns those models.

**Example:** Create a fake Stripe customer fixture rather than a host application's Eloquent user.

> Severity for plan review: **BLOCK**.
