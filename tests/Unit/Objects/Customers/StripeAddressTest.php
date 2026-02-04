<?php



use EncoreDigitalGroup\Stripe\Objects\Support\StripeAddress;

test("can create address with all fields", function (): void {
    $address = StripeAddress::make()
        ->withLine1("123 Main St")
        ->withLine2("Apt 4B")
        ->withCity("San Francisco")
        ->withState("CA")
        ->withPostalCode("94102")
        ->withCountry("US");

    expect($address->line1())->toBe("123 Main St")
        ->and($address->line2())->toBe("Apt 4B")
        ->and($address->city())->toBe("San Francisco")
        ->and($address->state())->toBe("CA")
        ->and($address->postalCode())->toBe("94102")
        ->and($address->country())->toBe("US");
});

test("can create address with minimal fields", function (): void {
    $address = StripeAddress::make()
        ->withLine1("456 Oak Ave")
        ->withCity("Portland");

    expect($address->line1())->toBe("456 Oak Ave")
        ->and($address->city())->toBe("Portland")
        ->and($address->line2())->toBeNull()
        ->and($address->state())->toBeNull();
});

test("toArray converts postalCode to postal_code", function (): void {
    $address = StripeAddress::make()
        ->withLine1("123 Main St")
        ->withCity("Seattle")
        ->withPostalCode("98101");

    $array = $address->toArray();

    expect($array)->toHaveKey("postal_code")
        ->and($array["postal_code"])->toBe("98101")
        ->and($array)->toHaveKey("line1")
        ->and($array["line1"])->toBe("123 Main St");
});