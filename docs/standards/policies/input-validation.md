# Input validation

Public package APIs validate input at their boundary before sending it to Stripe.

**Rules:**
- Keep validation and normalization in DTO construction or the owning public API.
- Internal methods may rely on the validated type contract.
- Preserve Stripe's validation failures when the API is the source of truth.

**Example:** Serialize a typed enum to its Stripe value only after its DTO has established a valid shape.

> Severity for plan review: **WARN**.
