# Strong typing

Public package behavior uses declared PHP types and named transfer shapes.

**Rules:**
- Declare parameter, return, and property types whenever PHP can express them.
- Use enums for closed Stripe value sets and DTOs for structured values.
- Do not expose loosely keyed arrays as a public transfer shape.

**Example:** Convert a Stripe SDK object into `StripeCustomer` rather than returning the SDK object or an array.

> Severity for plan review: **BLOCK**.
