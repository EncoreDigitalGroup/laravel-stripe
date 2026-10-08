# Configuration access

Environment and Laravel configuration access stays at the package configuration boundary.

**Rules:**
- Read configuration through `Stripe::config()` and types in `src/php/Support/Config/`.
- Do not read environment values from service or DTO logic.
- Resolve container bindings with `app()` only after checking `app()->bound()`.

**Example:** Inject or resolve a configured `StripeClient`; do not read a secret key in a service method.

> Severity for plan review: **BLOCK**.
