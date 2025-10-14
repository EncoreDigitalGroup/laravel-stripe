<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeBankAccount;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\BankAccountBuilder;

describe("BankAccountBuilder", function (): void {
    test("can build a basic bank account", function (): void {
        $builder = new BankAccountBuilder();
        $bankAccount = $builder->build(
            id: "ba_123",
            displayName: "Chase Checking",
            institutionName: "Chase Bank"
        );

        expect($bankAccount)
            ->toBeInstanceOf(StripeBankAccount::class)
            ->and($bankAccount->id)->toBe("ba_123")
            ->and($bankAccount->displayName)->toBe("Chase Checking")
            ->and($bankAccount->institutionName)->toBe("Chase Bank");
    });

    test("can build bank account with all parameters", function (): void {
        $builder = new BankAccountBuilder();
        $bankAccount = $builder->build(
            id: "ba_456",
            category: "checking",
            created: 1640995200,
            displayName: "Wells Fargo Savings",
            institutionName: "Wells Fargo",
            last4: "1234",
            liveMode: true,
            permissions: ["payment_method", "balances"],
            subscriptions: ["transactions"],
            supportedPaymentMethodTypes: ["us_bank_account"]
        );

        expect($bankAccount)
            ->toBeInstanceOf(StripeBankAccount::class)
            ->and($bankAccount->id)->toBe("ba_456")
            ->and($bankAccount->category)->toBe("checking")
            ->and($bankAccount->created)->toBe(1640995200)
            ->and($bankAccount->displayName)->toBe("Wells Fargo Savings")
            ->and($bankAccount->institutionName)->toBe("Wells Fargo")
            ->and($bankAccount->last4)->toBe("1234")
            ->and($bankAccount->liveMode)->toBeTrue()
            ->and($bankAccount->permissions)->toBe(["payment_method", "balances"])
            ->and($bankAccount->subscriptions)->toBe(["transactions"])
            ->and($bankAccount->supportedPaymentMethodTypes)->toBe(["us_bank_account"]);
    });

    test("can build bank account with minimal parameters", function (): void {
        $builder = new BankAccountBuilder();
        $bankAccount = $builder->build();

        expect($bankAccount)
            ->toBeInstanceOf(StripeBankAccount::class)
            ->and($bankAccount->id)->toBeNull()
            ->and($bankAccount->permissions)->toBe([])
            ->and($bankAccount->subscriptions)->toBe([])
            ->and($bankAccount->supportedPaymentMethodTypes)->toBe([]);
    });
});