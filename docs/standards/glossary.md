# Glossary

## Product and domain

- **Stripe for Laravel** — A typed PHP package that wraps the Stripe PHP SDK for Laravel applications.
- **DTO** — A typed package object that represents Stripe request or response data.
- **Stripe SDK** — The official `stripe/stripe-php` client used at the package boundary.
- **Stripe fake** — The package test client that records Stripe API requests and returns configured fixtures.

## Internal components

- **Services** — Types under `src/php/Services/` that call the Stripe SDK and return package DTOs.
- **Objects** — Types under `src/php/Objects/` that convert between Stripe SDK objects and typed package values.
- **StripeFixtures** — Shared realistic Stripe SDK payloads for tests.

## Tooling and runtime

- **Pest** — The package test framework.
- **PHPStan** — Static analyzer configured at level 8 through Larastan.
- **Duster** — The CI-owned PHP formatter and style linter.
- **GitLab CI** — The continuous integration system that runs quality and release pipelines.
- **Host logging sink** — The Laravel application's configured log channel and aggregation system; this library does not select one.

## Process and conventions

- **ADR** — Architecture Decision Record. A durable explanation of a significant technical choice in `docs/standards/decisions/`.
