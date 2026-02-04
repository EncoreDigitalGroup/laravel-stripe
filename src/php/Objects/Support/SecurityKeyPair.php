<?php

namespace EncoreDigitalGroup\Stripe\Objects\Support;

use EncoreDigitalGroup\StdLib\Objects\Support\Types\Number;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Str;
use Illuminate\Support\Facades\Cache;

class SecurityKeyPair
{
    public const string SECURITY_CACHE_KEY_PREFIX = "stripe.financialConnection.security.keyPair";

    public string $publicKey;
    public string $privateKey;
    public int|string $tenantId;

    public static function generate(string $stripeCustomerId, int $tenantId, int $ttlInMinutes = 60): void
    {
        $securityKey = self::make();
        $securityKey->tenantId = $tenantId;

        self::put($stripeCustomerId, $securityKey, $ttlInMinutes);
    }

    public static function make(): SecurityKeyPair
    {
        $securityKey = new self;
        $securityKey->publicKey = Str::guid();
        $securityKey->privateKey = Str::guid();

        return $securityKey;
    }

    private static function put(string $stripeCustomerId, SecurityKeyPair $securityKey, int $ttlInMinutes = 60): void
    {
        Cache::put(self::publicCacheKey($stripeCustomerId), $securityKey->publicKey, $ttlInMinutes);
        Cache::put(self::privateCacheKey($stripeCustomerId), $securityKey->privateKey, $ttlInMinutes);
        Cache::put(self::matchCacheKey($securityKey->publicKey), $securityKey->privateKey, $ttlInMinutes);
        Cache::put(self::tenantCacheKey($securityKey->publicKey), $securityKey->tenantId, $ttlInMinutes);
    }

    private static function publicCacheKey(string $stripeCustomerId): string
    {
        return self::SECURITY_CACHE_KEY_PREFIX . ".public.{$stripeCustomerId}";
    }

    private static function privateCacheKey(string $stripeCustomerId): string
    {
        return self::SECURITY_CACHE_KEY_PREFIX . ".private.{$stripeCustomerId}";
    }

    private static function matchCacheKey(string $publicKey): string
    {
        return self::SECURITY_CACHE_KEY_PREFIX . ".match.{$publicKey}";
    }

    private static function tenantCacheKey(string $publicKey): string
    {
        return self::SECURITY_CACHE_KEY_PREFIX . ".tenant.{$publicKey}";
    }

    public static function validate(string $stripeCustomerId, string $publicKey, string $privateKey): bool
    {
        $securityKey = self::get($stripeCustomerId);

        if ($securityKey->publicKey !== $publicKey) {
            return false;
        }

        if ($securityKey->privateKey !== $privateKey) {
            return false;
        }

        $cachedPrivateKey = Cache::get(self::matchCacheKey($securityKey->publicKey)); // Public Key is the CacheKey, Private Key is the CacheValue

        return $securityKey->privateKey === $cachedPrivateKey; // True if all checks match, false if even one fails;
    }

    public static function get(string $stripeCustomerId): SecurityKeyPair
    {
        $publicKey = Cache::get(self::publicCacheKey($stripeCustomerId));
        $privateKey = Cache::get(self::privateCacheKey($stripeCustomerId));
        $tenantId = Cache::get(self::tenantCacheKey($publicKey));
        $tenantId = Number::toInt($tenantId);

        $securityKey = self::make();

        $securityKey->publicKey = $publicKey;
        $securityKey->privateKey = $privateKey;
        $securityKey->tenantId = $tenantId;

        return $securityKey;
    }

    public static function flush(string $stripeCustomerId): void
    {
        $publicKey = Cache::get(self::publicCacheKey($stripeCustomerId));

        Cache::forget(self::publicCacheKey($stripeCustomerId));
        Cache::forget(self::privateCacheKey($stripeCustomerId));
        Cache::forget(self::matchCacheKey($publicKey));
        Cache::forget(self::tenantCacheKey($publicKey));
    }
}