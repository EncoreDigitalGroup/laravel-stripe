# Error handling

Exceptions represent exceptional failures and Stripe SDK failures propagate unless the package contract adds useful context.

**Rules:**
- Catch the narrowest exception type possible.
- Do not swallow an exception or return false success after a failure.
- Represent expected optional values with nullable types rather than exceptions.

**Example:** Return `?StripeCustomer` for an optional relationship, but let a Stripe request failure reach the caller.

> Severity for plan review: **BLOCK**.
