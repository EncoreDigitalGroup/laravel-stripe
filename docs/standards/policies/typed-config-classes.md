# Typed config classes

Package configuration loads once into typed classes under `src/php/Support/Config/`.

**Rules:**
- Keep configuration properties typed and named for their Stripe purpose.
- Do not turn config classes into per-read getters over raw arrays.
- Pass typed configuration to package services and client construction.

**Example:** Read `authentication->secretKey` from the typed Stripe configuration when building a client.

> Severity for plan review: **BLOCK**.
