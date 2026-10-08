# Static analysis

PHPStan with Larastan runs at level 8 and findings must be fixed at their root cause.

**Rules:**
- Run `./vendor/bin/phpstan analyse` before calling a code change complete.
- Keep analysis scoped to `src/php` as configured in `phpstan.neon`.
- Do not add ignores, unsafe casts, or suppressions to silence a real finding.

**Example:** Narrow a nullable Stripe SDK value before dereferencing it instead of suppressing the nullability error.

> Severity for plan review: **BLOCK**.
