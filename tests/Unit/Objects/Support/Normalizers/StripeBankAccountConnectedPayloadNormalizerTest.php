<?php

use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeBankAccount;
use EncoreDigitalGroup\Stripe\Objects\Support\Normalizers\StripeBankAccountConnectedPayloadNormalizer;
use EncoreDigitalGroup\Stripe\Objects\Support\SecurityKeyPair;
use EncoreDigitalGroup\Stripe\Objects\Support\StripeBankAccountConnectedPayload;

test("can normalize StripeBankAccountConnectedPayload to array", function (): void {
    $securityKeys = new SecurityKeyPair;
    $securityKeys->publicKey = "pub_key_123";
    $securityKeys->privateKey = "priv_key_123";

    $bankAccount = StripeBankAccount::make()->withId("ba_123");

    $payload = new StripeBankAccountConnectedPayload;
    $payload->setStripeCustomerId("cus_123");
    $payload->setSecurityKeys([
        "publicKey" => "pub_key_123",
        "privateKey" => "priv_key_123",
    ]);
    $payload->accounts = [$bankAccount];

    $normalizer = new StripeBankAccountConnectedPayloadNormalizer;
    $result = $normalizer->normalize($payload);

    expect($result)->toHaveKey("stripeCustomerId")
        ->and($result["stripeCustomerId"])->toBe("cus_123")
        ->and($result)->toHaveKey("accounts")
        ->and($result["accounts"])->toBeArray();
});

test("normalize handles payload without security keys", function (): void {
    $payload = new StripeBankAccountConnectedPayload;
    $payload->setStripeCustomerId("cus_456");
    $payload->accounts = [];

    $normalizer = new StripeBankAccountConnectedPayloadNormalizer;
    $result = $normalizer->normalize($payload);

    expect($result["stripeCustomerId"])->toBe("cus_456")
        ->and($result["accounts"])->toBe([]);
});

test("normalize throws exception for invalid type", function (): void {
    $normalizer = new StripeBankAccountConnectedPayloadNormalizer;

    expect(fn (): array => $normalizer->normalize(new stdClass))
        ->toThrow(InvalidArgumentException::class, "The object must be an instance of StripeBankAccountConnectedPayload");
});

test("can denormalize array to StripeBankAccountConnectedPayload", function (): void {
    $data = [
        "stripeCustomerId" => "cus_789",
        "securityKeys" => [
            "publicKey" => "pub_789",
            "privateKey" => "priv_789",
        ],
        "accounts" => [
            [
                "id" => "ba_789",
                "display_name" => "Test Bank",
            ],
        ],
    ];

    $normalizer = new StripeBankAccountConnectedPayloadNormalizer;
    $result = $normalizer->denormalize($data, StripeBankAccountConnectedPayload::class);

    expect($result)->toBeInstanceOf(StripeBankAccountConnectedPayload::class)
        ->and($result->getStripeCustomerId())->toBe("cus_789")
        ->and($result->getSecurityKeys())->toBeInstanceOf(SecurityKeyPair::class)
        ->and($result->getSecurityKeys()->publicKey)->toBe("pub_789")
        ->and($result->accounts)->toHaveCount(1)
        ->and($result->accounts[0])->toBeInstanceOf(StripeBankAccount::class);
});

test("denormalize handles payload that is already correct type", function (): void {
    $payload = new StripeBankAccountConnectedPayload;
    $payload->setStripeCustomerId("cus_existing");
    $payload->accounts = [];

    $normalizer = new StripeBankAccountConnectedPayloadNormalizer;
    $result = $normalizer->denormalize($payload, StripeBankAccountConnectedPayload::class);

    expect($result)->toBeInstanceOf(StripeBankAccountConnectedPayload::class)
        ->and($result->getStripeCustomerId())->toBe("cus_existing");
});

test("denormalize handles payload without security keys", function (): void {
    $data = [
        "stripeCustomerId" => "cus_no_keys",
        "accounts" => [],
    ];

    $normalizer = new StripeBankAccountConnectedPayloadNormalizer;
    $result = $normalizer->denormalize($data, StripeBankAccountConnectedPayload::class);

    expect($result->getStripeCustomerId())->toBe("cus_no_keys")
        ->and($result->getSecurityKeys())->toBeNull();
});

test("supports normalization for StripeBankAccountConnectedPayload", function (): void {
    $normalizer = new StripeBankAccountConnectedPayloadNormalizer;
    $payload = new StripeBankAccountConnectedPayload;

    expect($normalizer->supportsNormalization($payload))->toBeTrue()
        ->and($normalizer->supportsNormalization(new stdClass))->toBeFalse();
});

test("supports denormalization for StripeBankAccountConnectedPayload class", function (): void {
    $normalizer = new StripeBankAccountConnectedPayloadNormalizer;

    expect($normalizer->supportsDenormalization([], StripeBankAccountConnectedPayload::class))->toBeTrue()
        ->and($normalizer->supportsDenormalization([], StripeBankAccountConnectedPayload::class . "[]"))->toBeTrue()
        ->and($normalizer->supportsDenormalization([], stdClass::class))->toBeFalse();
});

test("getSupportedTypes returns correct types", function (): void {
    $normalizer = new StripeBankAccountConnectedPayloadNormalizer;
    $types = $normalizer->getSupportedTypes(null);

    expect($types)->toHaveKey(StripeBankAccountConnectedPayload::class)
        ->and($types)->toHaveKey(StripeBankAccountConnectedPayload::class . "[]")
        ->and($types[StripeBankAccountConnectedPayload::class])->toBeTrue();
});

test("denormalize handles payload instance with security keys", function (): void {
    $payload = new StripeBankAccountConnectedPayload;
    $payload->setStripeCustomerId("cus_from_instance");
    $payload->setSecurityKeys([
        "publicKey" => "pub_instance",
        "privateKey" => "priv_instance",
    ]);
    $payload->accounts = [];

    $normalizer = new StripeBankAccountConnectedPayloadNormalizer;
    $result = $normalizer->denormalize($payload, StripeBankAccountConnectedPayload::class);

    expect($result)->toBeInstanceOf(StripeBankAccountConnectedPayload::class)
        ->and($result->getStripeCustomerId())->toBe("cus_from_instance")
        ->and($result->getSecurityKeys())->toBeInstanceOf(SecurityKeyPair::class)
        ->and($result->getSecurityKeys()->publicKey)->toBe("pub_instance")
        ->and($result->getSecurityKeys()->privateKey)->toBe("priv_instance");
});
