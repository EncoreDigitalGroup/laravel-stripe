<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeBankAccount;
use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeTransactionRefresh;
use EncoreDigitalGroup\Stripe\Objects\Support\Normalizers\StripeBankAccountNormalizer;

test("can normalize StripeBankAccount to array", function (): void {
    $transactionRefresh = new StripeTransactionRefresh;
    $transactionRefresh->id = "txn_ref_123";

    $bankAccount = new StripeBankAccount;
    $bankAccount->id = "ba_123";
    $bankAccount->category = "cash";
    $bankAccount->created = 1234567890;
    $bankAccount->displayName = "Test Bank";
    $bankAccount->institutionName = "Test Institution";
    $bankAccount->last4 = "1234";
    $bankAccount->liveMode = true;
    $bankAccount->permissions = ["transactions"];
    $bankAccount->subscriptions = ["sub_1"];
    $bankAccount->supportedPaymentMethodTypes = ["us_bank_account"];
    $bankAccount->transactionRefresh = $transactionRefresh;

    $normalizer = new StripeBankAccountNormalizer;
    $result = $normalizer->normalize($bankAccount);

    expect($result)->toMatchArray([
        "id" => "ba_123",
        "category" => "cash",
        "created" => 1234567890,
        "display_name" => "Test Bank",
        "institution_name" => "Test Institution",
        "last4" => "1234",
        "livemode" => true,
        "permissions" => ["transactions"],
        "subscriptions" => ["sub_1"],
        "supported_payment_method_types" => ["us_bank_account"],
    ]);
});

test("normalize throws exception for invalid type", function (): void {
    $normalizer = new StripeBankAccountNormalizer;

    expect(fn (): array => $normalizer->normalize(new stdClass))
        ->toThrow(InvalidArgumentException::class, "The object must be an instance of StripeBankAccount");
});

test("can denormalize array to StripeBankAccount", function (): void {
    $data = [
        "id" => "ba_456",
        "category" => "credit",
        "created" => 1111111111,
        "display_name" => "My Bank",
        "institution_name" => "Big Bank",
        "last4" => "5678",
        "livemode" => false,
        "permissions" => ["balances", "transactions"],
        "subscriptions" => [],
        "supported_payment_method_types" => ["ach"],
    ];

    $normalizer = new StripeBankAccountNormalizer;
    $result = $normalizer->denormalize($data, StripeBankAccount::class);

    expect($result)->toBeInstanceOf(StripeBankAccount::class)
        ->and($result->id)->toBe("ba_456")
        ->and($result->category)->toBe("credit")
        ->and($result->displayName)->toBe("My Bank")
        ->and($result->last4)->toBe("5678")
        ->and($result->permissions)->toBe(["balances", "transactions"]);
});

test("denormalize handles transaction_refresh", function (): void {
    $data = [
        "id" => "ba_789",
        "transaction_refresh" => [
            "id" => "txn_ref_789",
            "status" => "pending",
            "last_attempted_at" => 1234567890,
            "next_refresh_available_at" => 1234567900,
        ],
    ];

    $normalizer = new StripeBankAccountNormalizer;
    $result = $normalizer->denormalize($data, StripeBankAccount::class);

    expect($result->transactionRefresh)->toBeInstanceOf(StripeTransactionRefresh::class)
        ->and($result->transactionRefresh->id)->toBe("txn_ref_789")
        ->and($result->transactionRefresh->status)->toBe("pending");
});

test("denormalize returns object if already correct type", function (): void {
    $bankAccount = new StripeBankAccount;
    $bankAccount->id = "ba_existing";

    $normalizer = new StripeBankAccountNormalizer;
    $result = $normalizer->denormalize($bankAccount, StripeBankAccount::class);

    expect($result)->toBe($bankAccount);
});

test("denormalize throws exception for non-array data", function (): void {
    $normalizer = new StripeBankAccountNormalizer;

    expect(fn (): \EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeBankAccount => $normalizer->denormalize("invalid", StripeBankAccount::class))
        ->toThrow(InvalidArgumentException::class, "Data must be an array for denormalization");
});

test("supports normalization for StripeBankAccount", function (): void {
    $normalizer = new StripeBankAccountNormalizer;
    $bankAccount = new StripeBankAccount;

    expect($normalizer->supportsNormalization($bankAccount))->toBeTrue()
        ->and($normalizer->supportsNormalization(new stdClass))->toBeFalse();
});

test("supports denormalization for StripeBankAccount class", function (): void {
    $normalizer = new StripeBankAccountNormalizer;

    expect($normalizer->supportsDenormalization([], StripeBankAccount::class))->toBeTrue()
        ->and($normalizer->supportsDenormalization([], StripeBankAccount::class . "[]"))->toBeTrue()
        ->and($normalizer->supportsDenormalization([], stdClass::class))->toBeFalse();
});

test("getSupportedTypes returns correct types", function (): void {
    $normalizer = new StripeBankAccountNormalizer;
    $types = $normalizer->getSupportedTypes(null);

    expect($types)->toHaveKey(StripeBankAccount::class)
        ->and($types)->toHaveKey(StripeBankAccount::class . "[]")
        ->and($types[StripeBankAccount::class])->toBeTrue();
});
