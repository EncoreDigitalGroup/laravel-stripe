<?php



use EncoreDigitalGroup\Stripe\Objects\Support\SecurityKeyPair;
use Illuminate\Support\Facades\Cache;

test("can create SecurityKeyPair with make method", function (): void {
    $keyPair = SecurityKeyPair::make();

    expect($keyPair)->toBeInstanceOf(SecurityKeyPair::class)
        ->and($keyPair->publicKey)->toBeString()
        ->and($keyPair->privateKey)->toBeString()
        ->and($keyPair->publicKey)->not->toBe($keyPair->privateKey);
});

test("make method generates unique keys each time", function (): void {
    $keyPair1 = SecurityKeyPair::make();
    $keyPair2 = SecurityKeyPair::make();

    expect($keyPair1->publicKey)->not->toBe($keyPair2->publicKey)
        ->and($keyPair1->privateKey)->not->toBe($keyPair2->privateKey);
});

test("can generate and cache security keys", function (): void {
    Cache::spy();

    SecurityKeyPair::generate("cus_test123", 42, 60);

    Cache::shouldHaveReceived("put")
        ->with("stripe.financialConnection.security.keyPair.public.cus_test123", Mockery::type("string"), 60)
        ->once();

    Cache::shouldHaveReceived("put")
        ->with("stripe.financialConnection.security.keyPair.private.cus_test123", Mockery::type("string"), 60)
        ->once();
});

test("can retrieve cached security keys", function (): void {
    Cache::shouldReceive("get")
        ->with("stripe.financialConnection.security.keyPair.public.cus_retrieve123")
        ->andReturn("pub_test_key");

    Cache::shouldReceive("get")
        ->with("stripe.financialConnection.security.keyPair.private.cus_retrieve123")
        ->andReturn("priv_test_key");

    Cache::shouldReceive("get")
        ->with("stripe.financialConnection.security.keyPair.tenant.pub_test_key")
        ->andReturn("99");

    $retrieved = SecurityKeyPair::get("cus_retrieve123");

    expect($retrieved)->toBeInstanceOf(SecurityKeyPair::class)
        ->and($retrieved->publicKey)->toBe("pub_test_key")
        ->and($retrieved->privateKey)->toBe("priv_test_key")
        ->and($retrieved->tenantId)->toBe(99);
});

test("validate returns true for matching keys", function (): void {
    Cache::shouldReceive("get")
        ->with("stripe.financialConnection.security.keyPair.public.cus_valid123")
        ->andReturn("pub_valid_key");

    Cache::shouldReceive("get")
        ->with("stripe.financialConnection.security.keyPair.private.cus_valid123")
        ->andReturn("priv_valid_key");

    Cache::shouldReceive("get")
        ->with("stripe.financialConnection.security.keyPair.tenant.pub_valid_key")
        ->andReturn("50");

    Cache::shouldReceive("get")
        ->with("stripe.financialConnection.security.keyPair.match.pub_valid_key")
        ->andReturn("priv_valid_key");

    $isValid = SecurityKeyPair::validate("cus_valid123", "pub_valid_key", "priv_valid_key");

    expect($isValid)->toBeTrue();
});

test("validate returns false for mismatched public key", function (): void {
    Cache::shouldReceive("get")
        ->with("stripe.financialConnection.security.keyPair.public.cus_invalid1")
        ->andReturn("pub_correct_key");

    Cache::shouldReceive("get")
        ->with("stripe.financialConnection.security.keyPair.private.cus_invalid1")
        ->andReturn("priv_correct_key");

    Cache::shouldReceive("get")
        ->with("stripe.financialConnection.security.keyPair.tenant.pub_correct_key")
        ->andReturn("50");

    $isValid = SecurityKeyPair::validate("cus_invalid1", "wrong_public_key", "priv_correct_key");

    expect($isValid)->toBeFalse();
});

test("validate returns false for mismatched private key", function (): void {
    Cache::shouldReceive("get")
        ->with("stripe.financialConnection.security.keyPair.public.cus_invalid2")
        ->andReturn("pub_correct_key");

    Cache::shouldReceive("get")
        ->with("stripe.financialConnection.security.keyPair.private.cus_invalid2")
        ->andReturn("priv_correct_key");

    Cache::shouldReceive("get")
        ->with("stripe.financialConnection.security.keyPair.tenant.pub_correct_key")
        ->andReturn("50");

    $isValid = SecurityKeyPair::validate("cus_invalid2", "pub_correct_key", "wrong_private_key");

    expect($isValid)->toBeFalse();
});

test("flush removes all cached keys for customer", function (): void {
    Cache::shouldReceive("get")
        ->with("stripe.financialConnection.security.keyPair.public.cus_flush123")
        ->andReturn("pub_flush_key");

    Cache::shouldReceive("forget")
        ->with("stripe.financialConnection.security.keyPair.public.cus_flush123")
        ->once();

    Cache::shouldReceive("forget")
        ->with("stripe.financialConnection.security.keyPair.private.cus_flush123")
        ->once();

    Cache::shouldReceive("forget")
        ->with("stripe.financialConnection.security.keyPair.match.pub_flush_key")
        ->once();

    Cache::shouldReceive("forget")
        ->with("stripe.financialConnection.security.keyPair.tenant.pub_flush_key")
        ->once();

    SecurityKeyPair::flush("cus_flush123");
});

test("cache keys use correct prefix", function (): void {
    expect(SecurityKeyPair::SECURITY_CACHE_KEY_PREFIX)->toBe("stripe.financialConnection.security.keyPair");
});

test("can set tenant id as int or string", function (): void {
    $keyPair = SecurityKeyPair::make();
    $keyPair->tenantId = 123;

    expect($keyPair->tenantId)->toBe(123);

    $keyPair->tenantId = "456";

    expect($keyPair->tenantId)->toBe("456");
});

test("generate respects custom TTL", function (): void {
    Cache::spy();

    SecurityKeyPair::generate("cus_ttl123", 88, 120);

    Cache::shouldHaveReceived("put")
        ->with("stripe.financialConnection.security.keyPair.public.cus_ttl123", Mockery::type("string"), 120)
        ->once();
});
