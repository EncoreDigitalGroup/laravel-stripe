<?php

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeTransactionRefresh;

describe("StripeTransactionRefresh", function (): void {
    test("can create StripeTransactionRefresh using make method", function (): void {
        $lastAttemptedAt = CarbonImmutable::now();
        $nextRefreshAvailableAt = $lastAttemptedAt->addDay();

        $transactionRefresh = StripeTransactionRefresh::make()
            ->withId("tr_123")
            ->withLastAttemptedAt($lastAttemptedAt)
            ->withNextRefreshAvailableAt($nextRefreshAvailableAt)
            ->withStatus("succeeded");

        expect($transactionRefresh)
            ->toBeInstanceOf(StripeTransactionRefresh::class)
            ->and($transactionRefresh->id())->toBe("tr_123")
            ->and($transactionRefresh->lastAttemptedAt()->timestamp)->toBe($lastAttemptedAt->timestamp)
            ->and($transactionRefresh->nextRefreshAvailableAt()->timestamp)->toBe($nextRefreshAvailableAt->timestamp)
            ->and($transactionRefresh->status())->toBe("succeeded");
    });

    test("can create StripeTransactionRefresh with partial parameters", function (): void {
        $transactionRefresh = StripeTransactionRefresh::make()
            ->withStatus("pending");

        expect($transactionRefresh)
            ->toBeInstanceOf(StripeTransactionRefresh::class)
            ->and($transactionRefresh->id())->toBeNull()
            ->and($transactionRefresh->lastAttemptedAt())->toBeNull()
            ->and($transactionRefresh->nextRefreshAvailableAt())->toBeNull()
            ->and($transactionRefresh->status())->toBe("pending");
    });

    test("can create StripeTransactionRefresh with no parameters", function (): void {
        $transactionRefresh = StripeTransactionRefresh::make();

        expect($transactionRefresh)
            ->toBeInstanceOf(StripeTransactionRefresh::class)
            ->and($transactionRefresh->id())->toBeNull()
            ->and($transactionRefresh->lastAttemptedAt())->toBeNull()
            ->and($transactionRefresh->nextRefreshAvailableAt())->toBeNull()
            ->and($transactionRefresh->status())->toBeNull();
    });

    test("toArray returns correct structure", function (): void {
        $lastAttemptedAt = CarbonImmutable::now();
        $nextRefreshAvailableAt = $lastAttemptedAt->addDay();

        $transactionRefresh = StripeTransactionRefresh::make()
            ->withId("tr_456")
            ->withLastAttemptedAt($lastAttemptedAt)
            ->withNextRefreshAvailableAt($nextRefreshAvailableAt)
            ->withStatus("failed");

        $array = $transactionRefresh->toArray();

        expect($array)
            ->toBeArray()
            ->and($array["id"])->toBe("tr_456")
            ->and($array["last_attempted_at"])->toBe($lastAttemptedAt->timestamp)
            ->and($array["next_refresh_available_at"])->toBe($nextRefreshAvailableAt->timestamp)
            ->and($array["status"])->toBe("failed");
    });

    test("toArray filters null values", function (): void {
        $transactionRefresh = StripeTransactionRefresh::make()
            ->withStatus("pending");

        $array = $transactionRefresh->toArray();

        expect($array)
            ->toBeArray()
            ->and($array)->toHaveKey("status")
            ->and($array["status"])->toBe("pending")
            ->and($array)->not->toHaveKey("id")
            ->and($array)->not->toHaveKey("last_attempted_at")
            ->and($array)->not->toHaveKey("next_refresh_available_at");
    });
});