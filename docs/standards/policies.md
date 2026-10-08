# Coding policies

This index routes contributors to the policies for `encoredigitalgroup/stripe`. Policies marked `BLOCK` need an explicit override; `WARN` policies need review.

## Mandatory policies (always read)

| Policy | Reason |
|---|---|
| [Code comments](policies/code-comments.md) | Every edit changes or preserves comments. |
| [Code style](policies/code-style.md) | Every edit must respect CI-owned formatting. |
| [Simplicity first](policies/simplicity-first.md) | Every design needs a proportional scope. |
| [Version control](policies/version-control.md) | Every change follows the repository workflow. |
| [Documentation](policies/documentation.md) | Every public change needs accurate docs. |
| [Static analysis](policies/static-analysis.md) | PHPStan is required for code changes. |
| [Formatter authority](policies/formatter-authority.md) | Agents must not run style tools. |
| [Naming and casing](policies/naming-and-casing.md) | Names form the package's public API. |
| [Strong typing](policies/strong-typing.md) | Typed boundaries are the package's value. |
| [Testing](policies/testing.md) | Behavioral changes need Pest coverage. |
| [Dependency licensing](policies/dependency-licensing.md) | Dependencies must remain license-compatible. |

See [the glossary](glossary.md) for package terms.

## Code style and language

| Policy | Severity |
|---|---|
| [Code style](policies/code-style.md) | BLOCK |
| [Code comments](policies/code-comments.md) | BLOCK |
| [Static analysis](policies/static-analysis.md) | BLOCK |
| [Formatter authority](policies/formatter-authority.md) | BLOCK |
| [Naming and casing](policies/naming-and-casing.md) | BLOCK |
| [Simplicity first](policies/simplicity-first.md) | WARN |
| [No magic values](policies/no-magic-values.md) | WARN |
| [Strong typing](policies/strong-typing.md) | BLOCK |
| [Carbon immutable only](policies/carbon-immutable-only.md) | WARN |
| [Illuminate over standard helpers](policies/illuminate-over-stdlib.md) | WARN |
| [No agent final](policies/no-agent-final.md) | WARN |

## Data and configuration

| Policy | Severity |
|---|---|
| [No raw SQL](policies/no-raw-sql.md) | BLOCK |
| [No DB facade](policies/no-db-facade.md) | BLOCK |
| [Eloquent versus DTOs](policies/eloquent-vs-dto.md) | WARN |
| [Configuration access](policies/configuration-access.md) | BLOCK |
| [Typed config classes](policies/typed-config-classes.md) | BLOCK |

## Logging, errors, and validation

| Policy | Severity |
|---|---|
| [Structured logging](policies/structured-logging.md) | BLOCK |
| [Ambient log context](policies/ambient-log-context.md) | WARN |
| [Error handling](policies/error-handling.md) | BLOCK |
| [Input validation](policies/input-validation.md) | WARN |

## Testing and documentation

| Policy | Severity |
|---|---|
| [Testing](policies/testing.md) | BLOCK |
| [Pest conventions](policies/pest-conventions.md) | BLOCK |
| [Test suite performance](policies/test-suite-performance.md) | BLOCK |
| [Eloquent factories only](policies/eloquent-factories-only.md) | BLOCK |
| [Test helper classes](policies/test-helper-classes.md) | BLOCK |
| [Documentation](policies/documentation.md) | BLOCK |

## Project workflow

| Policy | Severity |
|---|---|
| [Dependencies](policies/dependencies.md) | WARN |
| [Dependency licensing](policies/dependency-licensing.md) | BLOCK |
| [Version control](policies/version-control.md) | WARN |
