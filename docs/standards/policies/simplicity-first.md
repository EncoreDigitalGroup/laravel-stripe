# Simplicity first

Changes use the smallest design that fulfills the current package contract.

**Rules:**
- Do not add an abstraction for one caller.
- Do not add compatibility shims, flags, or extension points without a concrete consumer.
- Keep related behavior in one method unless reuse makes an extraction clearer.

**Example:** Add an operation to the existing service pattern instead of introducing a new service layer.

> Severity for plan review: **WARN**.
