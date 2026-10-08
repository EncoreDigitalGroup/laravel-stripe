# Dependencies

New dependencies need a concrete package-level reason and must fit the Laravel component stack.

**Rules:**
- Prefer existing Illuminate, Stripe SDK, and organization package capabilities before adding a dependency.
- Check maintenance, compatibility with PHP 8.4 and supported Illuminate versions, and licensing.
- Do not add a dependency for a small helper the package can express clearly itself.

**Example:** Use the existing `encoredigitalgroup/stdlib` helper when it meets the need instead of adding another collection utility.

> Severity for plan review: **WARN**.
