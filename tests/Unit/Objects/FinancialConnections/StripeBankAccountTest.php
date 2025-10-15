<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeBankAccount;
use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeTransactionRefresh;

describe("StripeBankAccount", function (): void {
    test("can create StripeBankAccount using make method", function (): void {
        $bankAccount = StripeBankAccount::make(
            id: "ba_123",
            category: "checking",
            displayName: "Chase Checking",
            institutionName: "Chase Bank",
            last4: "1234"
        );

        expect($bankAccount)
            ->toBeInstanceOf(StripeBankAccount::class)
            ->and($bankAccount->id)->toBe("ba_123")
            ->and($bankAccount->category)->toBe("checking")
            ->and($bankAccount->displayName)->toBe("Chase Checking")
            ->and($bankAccount->institutionName)->toBe("Chase Bank")
            ->and($bankAccount->last4)->toBe("1234");
    });

    test("can create StripeBankAccount with all parameters", function (): void {
        $transactionRefresh = StripeTransactionRefresh::make(status: "pending");
        $created = CarbonImmutable::createFromTimestamp(1640995200);

        $bankAccount = StripeBankAccount::make(
            id: "ba_456",
            category: "savings",
            created: $created,
            displayName: "Wells Fargo Savings",
            institutionName: "Wells Fargo",
            last4: "5678",
            liveMode: true,
            permissions: ["payment_method", "balances"],
            subscriptions: ["transactions"],
            supportedPaymentMethodTypes: ["us_bank_account"],
            transactionRefresh: $transactionRefresh
        );

        expect($bankAccount)
            ->toBeInstanceOf(StripeBankAccount::class)
            ->and($bankAccount->id)->toBe("ba_456")
            ->and($bankAccount->category)->toBe("savings")
            ->and($bankAccount->created)->toBe($created)
            ->and($bankAccount->displayName)->toBe("Wells Fargo Savings")
            ->and($bankAccount->institutionName)->toBe("Wells Fargo")
            ->and($bankAccount->last4)->toBe("5678")
            ->and($bankAccount->liveMode)->toBeTrue()
            ->and($bankAccount->permissions)->toBe(["payment_method", "balances"])
            ->and($bankAccount->subscriptions)->toBe(["transactions"])
            ->and($bankAccount->supportedPaymentMethodTypes)->toBe(["us_bank_account"])
            ->and($bankAccount->transactionRefresh)->toBe($transactionRefresh);
    });

    test("can create StripeBankAccount with defaults", function (): void {
        $bankAccount = StripeBankAccount::make();

        expect($bankAccount)
            ->toBeInstanceOf(StripeBankAccount::class)
            ->and($bankAccount->id)->toBeNull()
            ->and($bankAccount->permissions)->toBe([])
            ->and($bankAccount->subscriptions)->toBe([])
            ->and($bankAccount->supportedPaymentMethodTypes)->toBe([])
            ->and($bankAccount->transactionRefresh)->toBeNull();
    });

    test("toArray returns correct structure", function (): void {
        $bankAccount = StripeBankAccount::make(
            id: "ba_123",
            category: "checking",
            displayName: "Test Account",
            institutionName: "Test Bank",
            last4: "9999",
            liveMode: false,
            permissions: ["balances"]
        );

        $array = $bankAccount->toArray();

        expect($array)
            ->toBeArray()
            ->and($array['id'])->toBe("ba_123")
            ->and($array['category'])->toBe("checking")
            ->and($array['display_name'])->toBe("Test Account")
            ->and($array['institution_name'])->toBe("Test Bank")
            ->and($array['last4'])->toBe("9999")
            ->and($array['live_mode'])->toBeFalse()
            ->and($array['permissions'])->toBe(["balances"]);
    });

    test("toArray includes nested transaction refresh", function (): void {
        $transactionRefresh = StripeTransactionRefresh::make(
            id: "tr_123",
            status: "succeeded"
        );

        $bankAccount = StripeBankAccount::make(
            id: "ba_456",
            transactionRefresh: $transactionRefresh
        );

        $array = $bankAccount->toArray();

        expect($array['transaction_refresh'])
            ->toBeArray()
            ->and($array['transaction_refresh']['id'])->toBe("tr_123")
            ->and($array['transaction_refresh']['status'])->toBe("succeeded");
    });
});