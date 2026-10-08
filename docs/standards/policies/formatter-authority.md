# Formatter authority

CI owns formatting and style linting for this package.

**Rules:**
- A coding agent must not run `./vendor/bin/duster fix`, PHP-CS-Fixer, Rector, or another formatter, style linter, import sorter, or codemod in any mode.
- This includes `--check` and `--dry-run` modes.
- `./vendor/bin/phpstan analyse` is required static analysis, not formatting, and agents must run it.

**Example:** Write code to the established style and let CI apply or report style normalization.

> Severity for plan review: **BLOCK**.
