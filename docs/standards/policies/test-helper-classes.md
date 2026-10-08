# Test helper classes

Reusable test behavior belongs in named support classes, not file-level globals.

**Rules:**
- Put Stripe fakes and fixtures in `src/php/Support/Testing/`.
- Name a reusable helper by the behavior it provides.
- Keep a test-specific assertion inline when it has one caller.

**Example:** Extend `StripeFixtures` for a reusable SDK payload instead of building the payload in several test files.

> Severity for plan review: **BLOCK**.
