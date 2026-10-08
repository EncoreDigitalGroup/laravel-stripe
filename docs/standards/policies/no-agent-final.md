# No agent final

Agents do not add `final`, `private`, or `readonly` declarations to package code.

**Rules:**
- Preserve the package's existing extension and test seams.
- Add these restrictions only when a maintainer explicitly requests them.
- Do not use sealing as a substitute for clear contracts and tests.

**Example:** Add a typed public method to a service without making the class final.

> Severity for plan review: **WARN**.
