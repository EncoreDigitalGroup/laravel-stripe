<?php
/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    // ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

use Tests\Support\FakeStripeClient;

expect()->extend('toHaveCalledStripeMethod', function (string|\BackedEnum $method, ?array $expectedParams = null) {
    /** @var FakeStripeClient $fake */
    $fake = $this->value;

    if (!($fake instanceof FakeStripeClient)) {
        throw new InvalidArgumentException('Expected value to be an instance of FakeStripeClient');
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

expect()->extend('toNotHaveCalledStripeMethod', function (string|\BackedEnum $method) {
    /** @var FakeStripeClient $fake */
    $fake = $this->value;

    if (!($fake instanceof FakeStripeClient)) {
        throw new InvalidArgumentException('Expected value to be an instance of FakeStripeClient');
    }

    $stringMethod = $method instanceof \BackedEnum ? $method->value : $method;

    expect($fake->wasCalled($stringMethod))
        ->toBeFalse(
            "Expected Stripe method [{$stringMethod}] not to be called, but it was called {$fake->callCount($stringMethod)} time(s)."
        );

    return $this;
});

expect()->extend('toHaveCalledStripeMethodTimes', function (string|\BackedEnum $method, int $expectedCount) {
    /** @var FakeStripeClient $fake */
    $fake = $this->value;

    if (!($fake instanceof FakeStripeClient)) {
        throw new InvalidArgumentException('Expected value to be an instance of FakeStripeClient');
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

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

// No helper functions needed - use Stripe::fake() directly in tests
