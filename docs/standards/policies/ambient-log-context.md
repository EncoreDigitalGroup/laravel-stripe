# Ambient log context

The package leaves ambient Laravel log context to the host application.

**Rules:**
- Do not inject tenant, request, user, or host-specific context in this library.
- Include only operation-specific, non-secret context when the package logs.
- Let host applications use `Log::shareContext()` or their chosen mechanism for request-scoped context.

**Example:** Log a Stripe resource ID when safe, but do not assume a host request or tenant exists.

> Severity for plan review: **WARN**.
