# No DB facade

The package must not depend on Laravel's `DB` facade or database layer.

**Rules:**
- Do not call `DB`, database connections, transactions, or Eloquent from package code.
- Keep host persistence outside the package boundary.
- Test Stripe behavior with the package fake, not a database harness.

**Example:** Use `Stripe::fake()` to test an API request rather than creating database records.

> Severity for plan review: **BLOCK**.
