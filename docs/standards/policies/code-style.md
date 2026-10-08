# Code style

Formatting is owned by CI through `./vendor/bin/duster fix`; code must remain plain and follow the repository's existing conventions.

**Rules:**
- Do not run Duster, PHP-CS-Fixer, Rector, or another rewriting style tool locally.
- Use early returns, double-quoted strings, and arrow functions when they remain readable.
- Keep cognitive complexity at or below 10 per function and 50 per class.

**Example:** Prefer an early return over nesting the main path in a conditional.

> Severity for plan review: **BLOCK**.
