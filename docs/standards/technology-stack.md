# Technology stack

`composer.json` defines package constraints and scripts. `composer.lock` resolves the dependency tree. `.gitlab-ci.yml`, `phpstan.neon`, and `duster.json` define the
active CI and quality-tool configuration.

## Runtime and framework

| Technology         | Package / constraint | Role                          |
|--------------------|----------------------|-------------------------------|
| PHP                | `^8.4`               | Package runtime               |
| Illuminate Support | `^11 / ^12 / ^13`    | Laravel component integration |
| Stripe PHP SDK     | `^18`                | Stripe API client             |

## Platform and integrations

| Technology                  | Package / constraint | Role                           |
|-----------------------------|----------------------|--------------------------------|
| Encore Digital Group stdlib | `^2.0`               | Shared utilities               |
| PHPGenesis logger           | `^1`                 | Structured logging integration |

## Quality tooling

| Technology              | Package / constraint | Role                                                                            |
|-------------------------|----------------------|---------------------------------------------------------------------------------|
| Pest                    | `^4`                 | Tests via `./vendor/bin/pest --parallel`                                        |
| PHPStan and Larastan    | PHPStan level 8      | Static analysis via `./vendor/bin/phpstan analyse`                              |
| Duster and PHP-CS-Fixer | Duster `^3.0`        | CI-owned formatting; see [formatter authority](policies/formatter-authority.md) |
| GitLab CI               | `.gitlab-ci.yml`     | Code style, analysis, tests, build, and release                                 |
