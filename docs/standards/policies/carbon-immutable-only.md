# Carbon immutable only

Date values exposed by the package must not be mutated in place.

**Rules:**
- Use immutable date values for new package-facing date behavior.
- Return a new value when a date changes rather than changing a caller-owned object.
- Convert Stripe timestamps consistently through the package timestamp support.

**Example:** Derive a changed date as a new value rather than mutating a shared Carbon instance.

> Severity for plan review: **WARN**.
