<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeTransactionRefresh;
use EncoreDigitalGroup\Stripe\Objects\Support\Normalizers\StripeTransactionRefreshNormalizer;

test("can normalize StripeTransactionRefresh to array", function (): void {
    $lastAttemptedAt = CarbonImmutable::now();
    $nextRefreshAvailableAt = $lastAttemptedAt->addDay();

    $transactionRefresh = StripeTransactionRefresh::make()
        ->withId("txn_refresh_123")
        ->withLastAttemptedAt($lastAttemptedAt)
        ->withNextRefreshAvailableAt($nextRefreshAvailableAt)
        ->withStatus("pending");

    $normalizer = new StripeTransactionRefreshNormalizer;
    $result = $normalizer->normalize($transactionRefresh);

    expect($result)->toBe([
        "id" => "txn_refresh_123",
        "lastAttemptedAt" => $lastAttemptedAt,
        "nextRefreshAvailableAt" => $nextRefreshAvailableAt,
        "status" => "pending",
    ]);
});

test("normalize throws exception for invalid type", function (): void {
    $normalizer = new StripeTransactionRefreshNormalizer;

    expect(fn (): array => $normalizer->normalize(new stdClass))
        ->toThrow(InvalidArgumentException::class, "The object must be an instance of StripeTransactionRefresh");
});

test("can denormalize array to StripeTransactionRefresh", function (): void {
    $lastAttemptedAt = CarbonImmutable::now();
    $nextRefreshAvailableAt = $lastAttemptedAt->addDay();

    $data = [
        "id" => "txn_refresh_456",
        "last_attempted_at" => $lastAttemptedAt->timestamp,
        "next_refresh_available_at" => $nextRefreshAvailableAt->timestamp,
        "status" => "succeeded",
    ];

    $normalizer = new StripeTransactionRefreshNormalizer;
    $result = $normalizer->denormalize($data, StripeTransactionRefresh::class);

    expect($result)->toBeInstanceOf(StripeTransactionRefresh::class)
        ->and($result->id())->toBe("txn_refresh_456")
        ->and($result->lastAttemptedAt()->timestamp)->toBe($lastAttemptedAt->timestamp)
        ->and($result->nextRefreshAvailableAt()->timestamp)->toBe($nextRefreshAvailableAt->timestamp)
        ->and($result->status())->toBe("succeeded");
});

test("denormalize returns object if already correct type", function (): void {
    $transactionRefresh = StripeTransactionRefresh::make()->withId("txn_refresh_789");

    $normalizer = new StripeTransactionRefreshNormalizer;
    $result = $normalizer->denormalize($transactionRefresh, StripeTransactionRefresh::class);

    expect($result)->toBe($transactionRefresh);
});

test("denormalize throws exception for non-array data", function (): void {
    $normalizer = new StripeTransactionRefreshNormalizer;

    expect(fn (): StripeTransactionRefresh => $normalizer->denormalize("invalid", StripeTransactionRefresh::class))
        ->toThrow(InvalidArgumentException::class, "Data must be an array for denormalization");
});

test("supports normalization for StripeTransactionRefresh", function (): void {
    $normalizer = new StripeTransactionRefreshNormalizer;
    $transactionRefresh = StripeTransactionRefresh::make();

    expect($normalizer->supportsNormalization($transactionRefresh))->toBeTrue()
        ->and($normalizer->supportsNormalization(new stdClass))->toBeFalse();
});

test("supports denormalization for StripeTransactionRefresh class", function (): void {
    $normalizer = new StripeTransactionRefreshNormalizer;

    expect($normalizer->supportsDenormalization([], StripeTransactionRefresh::class))->toBeTrue()
        ->and($normalizer->supportsDenormalization([], stdClass::class))->toBeFalse();
});

test("getSupportedTypes returns correct types", function (): void {
    $normalizer = new StripeTransactionRefreshNormalizer;
    $types = $normalizer->getSupportedTypes(null);

    expect($types)->toHaveKey(StripeTransactionRefresh::class)
        ->and($types[StripeTransactionRefresh::class])->toBeTrue();
});
