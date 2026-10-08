# Structured logging

Diagnostic output uses `phpgenesis/logger` and the host application's Laravel logging configuration.

**Rules:**
- Use a static message and structured context; do not interpolate dynamic values into messages.
- Do not add stdout debugging to committed code.
- Package code does not choose a production sink; the host application owns its Laravel channel and aggregation sink.

## Write-side contract

Log Stripe operation context such as a resource ID or method name without logging credentials, payment details, or other secrets.

## Read-side playbook

Start with the static message, then filter structured context in the host application's configured log sink.

> Severity for plan review: **BLOCK**.
