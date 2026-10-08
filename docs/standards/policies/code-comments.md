# Code comments

Comments explain a non-obvious reason or constraint; code and names explain routine behavior.

**Rules:**
- Add a short comment only when a reader cannot infer the reason from the code.
- Keep PHPDoc only when it improves IDE support or PHPStan's type knowledge.
- Remove comments that restate a method or variable name.

**Example:** Explain a Stripe API constraint, not that a value is assigned.

> Severity for plan review: **BLOCK**.
