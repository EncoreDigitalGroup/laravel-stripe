# Illuminate over standard helpers

Use supported Illuminate helpers when they match the package's Laravel-component integration.

**Rules:**
- Prefer Illuminate collection and support utilities when an established package pattern uses them.
- Use PHP standard-library functions for direct language operations that do not need framework behavior.
- Do not add a helper dependency when Illuminate or existing package utilities provide the behavior.

**Example:** Use the established collection and support types instead of a second collection library.

> Severity for plan review: **WARN**.
