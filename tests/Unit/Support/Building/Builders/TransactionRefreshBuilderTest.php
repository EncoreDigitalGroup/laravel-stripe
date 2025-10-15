<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeTransactionRefresh;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\TransactionRefreshBuilder;

describe("TransactionRefreshBuilder", function (): void {
    test("can build a basic transaction refresh", function (): void {
        $builder = new TransactionRefreshBuilder;
        $transactionRefresh = $builder->build(
            status: "pending"
        );

        expect($transactionRefresh)
            ->toBeInstanceOf(StripeTransactionRefresh::class)
            ->and($transactionRefresh->status)->toBe("pending")
            ->and($transactionRefresh->id)->toBeNull();
    });

    test("can build transaction refresh with all parameters", function (): void {
        $builder = new TransactionRefreshBuilder;
        $transactionRefresh = $builder->build(
            id: "tr_123",
            lastAttemptedAt: 1640995200,
            nextRefreshAvailableAt: 1640995800,
            status: "succeeded"
        );

        expect($transactionRefresh)
            ->toBeInstanceOf(StripeTransactionRefresh::class)
            ->and($transactionRefresh->id)->toBe("tr_123")
            ->and($transactionRefresh->lastAttemptedAt)->toBe(1640995200)
            ->and($transactionRefresh->nextRefreshAvailableAt)->toBe(1640995800)
            ->and($transactionRefresh->status)->toBe("succeeded");
    });

    test("can build transaction refresh with minimal parameters", function (): void {
        $builder = new TransactionRefreshBuilder;
        $transactionRefresh = $builder->build();

        expect($transactionRefresh)
            ->toBeInstanceOf(StripeTransactionRefresh::class)
            ->and($transactionRefresh->id)->toBeNull()
            ->and($transactionRefresh->lastAttemptedAt)->toBeNull()
            ->and($transactionRefresh->nextRefreshAvailableAt)->toBeNull()
            ->and($transactionRefresh->status)->toBeNull();
    });
});