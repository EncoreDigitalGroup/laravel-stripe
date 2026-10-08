# Test suite performance

Tests avoid unnecessary database, network, and repeated fixture costs.

**Rules:**
- Use `Stripe::fake()` for every Stripe API interaction.
- Reuse `StripeFixtures` instead of assembling complete SDK payloads per test.
- Do not add database setup for behavior that the package can test through fakes and DTOs.

**Example:** Configure one fake response for the exercised method instead of constructing a live Stripe client.

> Severity for plan review: **BLOCK**.
