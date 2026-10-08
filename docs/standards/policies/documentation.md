# Documentation

Documentation describes the shipped package contract and stays synchronized with code.

**Rules:**
- Update the relevant file in `docs/` or `README.md` when public behavior changes.
- Add terms that are not obvious to a new contributor to `../glossary.md`.
- Add or amend one policy per file and update `../policies.md` when a durable engineering rule changes.
- Record durable architecture decisions in `../decisions/` rather than a temporary planning file.

**Example:** Document a new Stripe service operation with its typed input, output, and fake support.

> Severity for plan review: **BLOCK**.
