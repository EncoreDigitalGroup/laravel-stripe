# No raw SQL

This Laravel package does not issue raw SQL.

**Rules:**
- Do not introduce raw SQL, query-builder statements, or database connections into package logic.
- Keep Stripe data in typed DTOs and services rather than persistence models.
- Let host applications own persistence choices.

**Example:** Return `StripeSubscription` to the host instead of persisting Stripe fields in the package.

> Severity for plan review: **BLOCK**.
