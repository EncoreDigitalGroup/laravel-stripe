# Naming and casing

Names follow the package's PHP and Stripe-domain conventions.

**Rules:**
- Use camelCase for variables and methods.
- Use PascalCase for classes, paths, and enum cases.
- Use descriptive domain names instead of implementation-detail names.
- Use complete namespaces in imports unless a conflict requires an alias.

**Example:** Use `StripeMethod::CustomersCreate`, not `CUSTOMERS_CREATE` or an inline method string.

> Severity for plan review: **BLOCK**.
