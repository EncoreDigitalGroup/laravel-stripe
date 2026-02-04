<?php

namespace EncoreDigitalGroup\Stripe\Support\Traits;

use Carbon\CarbonImmutable;

/** @internal */
trait HasTimestamps
{
    protected static function timestampToCarbon(?int $timestamp): ?CarbonImmutable
    {
        if ($timestamp === null) {
            return null;
        }

        return CarbonImmutable::createFromTimestamp($timestamp);
    }

    protected static function carbonToTimestamp(?CarbonImmutable $carbon): ?int
    {
        return $carbon instanceof CarbonImmutable ? (int) $carbon->timestamp : null;
    }
}
