<?php



use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeBankAccount;
use EncoreDigitalGroup\Stripe\Objects\Support\SecurityKeyPair;
use EncoreDigitalGroup\Stripe\Objects\Support\StripeBankAccountConnectedPayload;

test("can create empty StripeBankAccountConnectedPayload", function (): void {
    $payload = new StripeBankAccountConnectedPayload;

    expect($payload)->toBeInstanceOf(StripeBankAccountConnectedPayload::class)
        ->and($payload->getStripeCustomerId())->toBeNull()
        ->and($payload->getSecurityKeys())->toBeNull();
});

test("can set and get stripe customer id", function (): void {
    $payload = new StripeBankAccountConnectedPayload;
    $result = $payload->setStripeCustomerId("cus_test123");

    expect($result)->toBe($payload)
        ->and($payload->getStripeCustomerId())->toBe("cus_test123");
});

test("can set and get security keys from array", function (): void {
    $payload = new StripeBankAccountConnectedPayload;
    $result = $payload->setSecurityKeys([
        "publicKey" => "pub_key_abc",
        "privateKey" => "priv_key_xyz",
    ]);

    expect($result)->toBe($payload)
        ->and($payload->getSecurityKeys())->toBeInstanceOf(SecurityKeyPair::class)
        ->and($payload->getSecurityKeys()->publicKey)->toBe("pub_key_abc")
        ->and($payload->getSecurityKeys()->privateKey)->toBe("priv_key_xyz");
});

test("can set accounts array", function (): void {
    $bankAccount = StripeBankAccount::make()->withId("ba_test123");

    $payload = new StripeBankAccountConnectedPayload;
    $payload->accounts = [$bankAccount];

    expect($payload->accounts)->toBeArray()
        ->and($payload->accounts)->toHaveCount(1)
        ->and($payload->accounts[0])->toBeInstanceOf(StripeBankAccount::class)
        ->and($payload->accounts[0]->id())->toBe("ba_test123");
});

test("can chain setters", function (): void {
    $payload = new StripeBankAccountConnectedPayload;

    $result = $payload
        ->setStripeCustomerId("cus_chain123")
        ->setSecurityKeys([
            "publicKey" => "pub_chain",
            "privateKey" => "priv_chain",
        ]);

    expect($result)->toBe($payload)
        ->and($payload->getStripeCustomerId())->toBe("cus_chain123")
        ->and($payload->getSecurityKeys()->publicKey)->toBe("pub_chain");
});

test("setSecurityKeys creates new SecurityKeyPair instance", function (): void {
    $payload = new StripeBankAccountConnectedPayload;

    $payload->setSecurityKeys([
        "publicKey" => "pub_1",
        "privateKey" => "priv_1",
    ]);

    $firstKeys = $payload->getSecurityKeys();

    $payload->setSecurityKeys([
        "publicKey" => "pub_2",
        "privateKey" => "priv_2",
    ]);

    $secondKeys = $payload->getSecurityKeys();

    expect($firstKeys)->not->toBe($secondKeys)
        ->and($secondKeys->publicKey)->toBe("pub_2")
        ->and($secondKeys->privateKey)->toBe("priv_2");
});

test("accounts array can contain multiple bank accounts", function (): void {
    $bankAccount1 = StripeBankAccount::make()->withId("ba_1");
    $bankAccount2 = StripeBankAccount::make()->withId("ba_2");
    $bankAccount3 = StripeBankAccount::make()->withId("ba_3");

    $payload = new StripeBankAccountConnectedPayload;
    $payload->accounts = [$bankAccount1, $bankAccount2, $bankAccount3];

    expect($payload->accounts)->toHaveCount(3)
        ->and($payload->accounts[0]->id())->toBe("ba_1")
        ->and($payload->accounts[1]->id())->toBe("ba_2")
        ->and($payload->accounts[2]->id())->toBe("ba_3");
});
