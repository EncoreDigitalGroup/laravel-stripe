# 0001 - Typed Financial Connections account operations

Status: Accepted

## Context

Stripe Financial Connections account operations return dynamic SDK objects. Hosts need a typed package boundary to retrieve an existing `fca_*` account and inspect its
lifecycle state without reaching into the raw Stripe client.

## Decision

Expose Financial Connections account retrieval through `Stripe::financialConnection()->account()->get()`, returning `StripeBankAccount`. Represent the known Stripe lifecycle
states with the nullable `FinancialConnectionsAccountStatus` enum. Convert SDK account objects directly at the DTO boundary and preserve Stripe request failures for the host.

## Consequences

Hosts receive typed account data and can distinguish active, inactive, and disconnected accounts. Omitted or unknown status values remain `null`. Stripe remains the source
of truth for account eligibility and request validation, and the host receives Stripe errors unchanged.
