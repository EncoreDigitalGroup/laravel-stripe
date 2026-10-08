# Financial Connections account operations plan

## Goal

Expose typed Stripe Financial Connections account operations so host applications can retrieve, refresh, and subscribe an existing `fca_*` account without starting a
new collection session. This supports true relinking for active accounts and lets hosts respond to Stripe account lifecycle events without raw SDK calls.

## Scope

- Add typed retrieval, transaction subscription, and on-demand transaction refresh operations for Financial Connections accounts.
- Add the account `status` to `StripeBankAccount` so hosts can distinguish active from inactive accounts.
- Extend the package fake client, fixtures, and Pest coverage for each new operation.

## Out of scope

- Financial Connections transaction import, checkpoint persistence, tenant handling, queue jobs, webhooks, and UI behavior. Those belong to the host application.
- A new Stripe API for reactivating an inactive account. Stripe exposes an `account.reactivated` event but no reactivation endpoint.
- Financial Connections session collection changes. Sessions remain the existing collection flow.

## Public API

Add `EncoreDigitalGroup\Stripe\Services\StripeFinancialConnectionsAccountService` using the existing `HasStripe` pattern:

- `get(string $accountId): StripeBankAccount` calls `financialConnections->accounts->retrieve()`.
- `subscribeToTransactions(string $accountId): StripeBankAccount` calls `financialConnections->accounts->subscribe()` with `features => ['transactions']`.
- `refreshTransactions(string $accountId): StripeBankAccount` calls `financialConnections->accounts->refresh()` with `features => ['transactions']`.

Extend `StripeBankAccount` and `StripeBankAccountNormalizer` with nullable `status`, `withStatus(string $status)`, and `status(): ?string`. Preserve all existing
normalization behavior for incomplete Stripe payloads.

Add `StripeMethod` cases for the three nested service calls. Extend the fake-client service access only as needed so a Financial Connections nested service resolves to
the exact method names and records the feature parameters. Add or extend `StripeFixtures::financialConnectionsAccount()` with a default active status.

## Implementation steps

1. Add the `status` property, fluent setter, getter, array serialization, and Stripe-object normalization to `StripeBankAccount` and its normalizer. Add unit tests for
   active status, inactive status, and a payload where status is absent.
2. Add `StripeFinancialConnectionsAccountService` under `src/php/Services/`, following the existing `StripeCustomerService` pattern. Convert SDK account responses to
   `StripeBankAccount`; do not expose raw Stripe SDK account objects.
3. Add the three `StripeMethod` enum cases and fake support for `financialConnections.accounts.retrieve`, `financialConnections.accounts.subscribe`, and
   `financialConnections.accounts.refresh`. Assert that subscription and refresh send only `features => ['transactions']`.
4. Add feature tests using `Stripe::fake()` and `StripeFixtures::financialConnectionsAccount()` for retrieval, subscription, refresh, typed results, and fake-call
   parameters. Test Stripe request errors propagate unchanged.
5. Update the package README or Financial Connections documentation to describe the new account operations and the active-account limitation. Do not document host
   application recovery behavior here.
6. Run `vendor/bin/pest --parallel` and the repository's configured static analysis. Release the package only after those checks pass.

## Acceptance criteria

- A host can retrieve an existing Financial Connections account as `StripeBankAccount` and read its `status()`.
- A host can subscribe or refresh transaction data for an existing active account through typed package APIs.
- Package consumers never need a raw `Stripe::client()->financialConnections->accounts` call for these operations.
- The package fake records all three calls and their `transactions` feature payloads.
- No session collection, host persistence, webhook, or tenant logic is added to the package.
