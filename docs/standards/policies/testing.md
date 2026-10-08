# Testing

Every behavioral change ships Pest coverage that asserts the package contract.

**Rules:**
- Run `./vendor/bin/pest --parallel` and keep the suite green.
- Put service behavior in `tests/Feature/` and DTO behavior in `tests/Unit/`.
- Assert observable behavior, types, and Stripe request parameters, not implementation wording.
- Use `test()` and group related cases with `describe()`.

**Example:** Assert that a service returns a typed object and the fake received the intended Stripe method.

> Severity for plan review: **BLOCK**.
