<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeTransactionRefresh;

describe("StripeTransactionRefresh", function (): void {
    test("can create StripeTransactionRefresh using make method", function (): void {
        $transactionRefresh = StripeTransactionRefresh::make(
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

    test("can create StripeTransactionRefresh with partial parameters", function (): void {
        $transactionRefresh = StripeTransactionRefresh::make(
            status: "pending"
        );

        expect($transactionRefresh)
            ->toBeInstanceOf(StripeTransactionRefresh::class)
            ->and($transactionRefresh->id)->toBeNull()
            ->and($transactionRefresh->lastAttemptedAt)->toBeNull()
            ->and($transactionRefresh->nextRefreshAvailableAt)->toBeNull()
            ->and($transactionRefresh->status)->toBe("pending");
    });

    test("can create StripeTransactionRefresh with no parameters", function (): void {
        $transactionRefresh = StripeTransactionRefresh::make();

        expect($transactionRefresh)
            ->toBeInstanceOf(StripeTransactionRefresh::class)
            ->and($transactionRefresh->id)->toBeNull()
            ->and($transactionRefresh->lastAttemptedAt)->toBeNull()
            ->and($transactionRefresh->nextRefreshAvailableAt)->toBeNull()
            ->and($transactionRefresh->status)->toBeNull();
    });

    test("toArray returns correct structure", function (): void {
        $transactionRefresh = StripeTransactionRefresh::make(
            id: "tr_456",
            lastAttemptedAt: 1640995200,
            nextRefreshAvailableAt: 1640995800,
            status: "failed"
        );

        $array = $transactionRefresh->toArray();

        expect($array)
            ->toBeArray()
            ->and($array['id'])->toBe("tr_456")
            ->and($array['last_attempted_at'])->toBe(1640995200)
            ->and($array['next_refresh_available_at'])->toBe(1640995800)
            ->and($array['status'])->toBe("failed");
    });

    test("toArray filters null values", function (): void {
        $transactionRefresh = StripeTransactionRefresh::make(
            status: "pending"
        );

        $array = $transactionRefresh->toArray();

        expect($array)
            ->toBeArray()
            ->and($array)->toHaveKey('status')
            ->and($array['status'])->toBe("pending")
            ->and($array)->not->toHaveKey('id')
            ->and($array)->not->toHaveKey('last_attempted_at')
            ->and($array)->not->toHaveKey('next_refresh_available_at');
    });
});