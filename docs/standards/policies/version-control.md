# Version control

Work happens on focused branches from `main`, with commits that explain why the change exists.

**Rules:**
- Do not commit directly to `main`.
- Keep commits focused and include related tests and documentation.
- Open a merge request and require GitLab CI code style, static analysis, and tests to pass.

**Example:** Separate an unrelated dependency update from a Stripe API behavior change.

> Severity for plan review: **WARN**.
