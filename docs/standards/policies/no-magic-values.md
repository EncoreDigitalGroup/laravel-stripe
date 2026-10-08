# No magic values

Stripe domain values must be named through an enum or constant when a stable type exists.

**Rules:**
- Put shared enums in `src/php/Enums/` and shared constants next to their owning type.
- Use string-backed enum values only at the Stripe API serialization boundary.
- Do not repeat status, type, interval, or operation strings in conditionals.

**Example:** Use `RecurringInterval::Month` instead of comparing to `"month"` in application logic.

> Severity for plan review: **WARN**.
