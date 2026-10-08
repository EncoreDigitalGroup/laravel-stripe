# Dependency licensing

Package dependencies must have a license compatible with the BSD-3-Clause package.

**Rules:**
- Do not add GPL, AGPL, or SSPL dependencies at any depth.
- Check the resolved Composer tree with `composer licenses` and each package's `license` field.
- Escalate uncertain license terms to Encore Digital Group engineering leadership before adding the dependency.

**Example:** Stop dependency work when a transitive license is incompatible instead of merging it for later review.

> Severity for plan review: **BLOCK**.
