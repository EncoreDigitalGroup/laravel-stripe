<?php
/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use Tests\Support\FakeStripeClient;

pest()->extend(Tests\TestCase::class)
    ->in("Feature")
    ->in("Unit");


expect()->extend("toHaveCalledStripeMethod", function (string|\BackedEnum $method, ?array $expectedParams = null) {
    /** @var FakeStripeClient $fake */
    $fake = $this->value;

    if (!($fake instanceof FakeStripeClient)) {
        throw new InvalidArgumentException("Expected value to be an instance of FakeStripeClient");
    }

    $stringMethod = $method instanceof \BackedEnum ? $method->value : $method;

    expect($fake->wasCalled($stringMethod))
        ->toBeTrue("Expected Stripe method [{$stringMethod}] to be called, but it was not.");

    if ($expectedParams !== null) {
        $actualParams = $fake->getCall($stringMethod);
        expect($actualParams)
            ->toBe(
                $expectedParams,
                "Expected Stripe method [{$stringMethod}] to be called with specific parameters.\n" .
                "Expected: " . json_encode($expectedParams, JSON_PRETTY_PRINT) . "\n" .
                "Actual: " . json_encode($actualParams, JSON_PRETTY_PRINT)
            );
    }

    return $this;
});

expect()->extend("toNotHaveCalledStripeMethod", function (string|\BackedEnum $method) {
    /** @var FakeStripeClient $fake */
    $fake = $this->value;

    if (!($fake instanceof FakeStripeClient)) {
        throw new InvalidArgumentException("Expected value to be an instance of FakeStripeClient");
    }

    $stringMethod = $method instanceof \BackedEnum ? $method->value : $method;

    expect($fake->wasCalled($stringMethod))
        ->toBeFalse(
            "Expected Stripe method [{$stringMethod}] not to be called, but it was called {$fake->callCount($stringMethod)} time(s)."
        );

    return $this;
});

expect()->extend("toHaveCalledStripeMethodTimes", function (string|\BackedEnum $method, int $expectedCount) {
    /** @var FakeStripeClient $fake */
    $fake = $this->value;

    if (!($fake instanceof FakeStripeClient)) {
        throw new InvalidArgumentException("Expected value to be an instance of FakeStripeClient");
    }

    $stringMethod = $method instanceof \BackedEnum ? $method->value : $method;
    $actualCount = $fake->callCount($stringMethod);

    expect($actualCount)
        ->toBe(
            $expectedCount,
            "Expected Stripe method [{$stringMethod}] to be called {$expectedCount} time(s), but it was called {$actualCount} time(s)."
        );

    return $this;
});
