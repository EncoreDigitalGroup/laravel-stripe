<?php



use Carbon\CarbonImmutable;
use EncoreDigitalGroup\Stripe\Enums\PaymentMethodType;
use EncoreDigitalGroup\Stripe\Enums\SetupIntentStatus;
use EncoreDigitalGroup\Stripe\Enums\SetupIntentUsage;
use EncoreDigitalGroup\Stripe\Objects\Payment\StripeSetupIntent;
use Illuminate\Support\Collection;
use Stripe\Util\Util;

test("can create StripeSetupIntent using make method", function (): void {
    $setupIntent = StripeSetupIntent::make()
        ->withCustomer("cus_123")
        ->withDescription("Test setup intent")
        ->withUsage(SetupIntentUsage::OffSession);

    expect($setupIntent)
        ->toBeInstanceOf(StripeSetupIntent::class)
        ->and($setupIntent->customer())->toBe("cus_123")
        ->and($setupIntent->description())->toBe("Test setup intent")
        ->and($setupIntent->usage())->toBe(SetupIntentUsage::OffSession);
});

test("can create StripeSetupIntent from Stripe object", function (): void {
    $stripeObject = Util::convertToStripeObject([
        "id" => "seti_123",
        "object" => "setup_intent",
        "customer" => "cus_456",
        "description" => "Setup for subscription",
        "payment_method" => "pm_abc",
        "status" => "succeeded",
        "usage" => "off_session",
        "created" => 1234567890,
        "client_secret" => "seti_123_secret_xyz",
        "payment_method_types" => ["card"],
        "metadata" => ["order_id" => "order_123"],
    ], null);

    $setupIntent = StripeSetupIntent::fromStripeObject($stripeObject);

    expect($setupIntent)
        ->toBeInstanceOf(StripeSetupIntent::class)
        ->and($setupIntent->id())->toBe("seti_123")
        ->and($setupIntent->customer())->toBe("cus_456")
        ->and($setupIntent->description())->toBe("Setup for subscription")
        ->and($setupIntent->paymentMethod())->toBe("pm_abc")
        ->and($setupIntent->status())->toBe(SetupIntentStatus::Succeeded)
        ->and($setupIntent->usage())->toBe(SetupIntentUsage::OffSession)
        ->and($setupIntent->created())->toBeInstanceOf(CarbonImmutable::class)
        ->and($setupIntent->clientSecret())->toBe("seti_123_secret_xyz")
        ->and($setupIntent->paymentMethodTypes())->toBeInstanceOf(Collection::class)
        ->and($setupIntent->paymentMethodTypes()->first())->toBe(PaymentMethodType::Card)
        ->and($setupIntent->metadata())->toBe(["order_id" => "order_123"]);
});

test("can convert StripeSetupIntent to array", function (): void {
    $setupIntent = StripeSetupIntent::make()
        ->withCustomer("cus_789")
        ->withDescription("Test setup")
        ->withStatus(SetupIntentStatus::RequiresPaymentMethod)
        ->withUsage(SetupIntentUsage::OnSession);

    $array = $setupIntent->toArray();

    expect($array)
        ->toBeArray()
        ->and($array["customer"])->toBe("cus_789")
        ->and($array["description"])->toBe("Test setup")
        ->and($array["status"])->toBe("requires_payment_method")
        ->and($array["usage"])->toBe("on_session");
});

test("toArray filters null values", function (): void {
    $setupIntent = StripeSetupIntent::make()
        ->withCustomer("cus_123");

    $array = $setupIntent->toArray();

    expect($array)->toHaveKey("customer")
        ->and($array)->not->toHaveKey("description")
        ->and($array)->not->toHaveKey("payment_method")
        ->and($array)->not->toHaveKey("status");
});

test("can set payment method types", function (): void {
    $setupIntent = StripeSetupIntent::make()
        ->withPaymentMethodTypes(collect([PaymentMethodType::Card, PaymentMethodType::UsBankAccount]));

    expect($setupIntent->paymentMethodTypes())->toHaveCount(2)
        ->and($setupIntent->paymentMethodTypes()->first())->toBe(PaymentMethodType::Card)
        ->and($setupIntent->paymentMethodTypes()->last())->toBe(PaymentMethodType::UsBankAccount);
});

test("can handle nested customer object", function (): void {
    $stripeObject = Util::convertToStripeObject([
        "id" => "seti_123",
        "object" => "setup_intent",
        "customer" => [
            "id" => "cus_nested",
            "object" => "customer",
            "email" => "test@example.com",
        ],
        "status" => "requires_payment_method",
    ], null);

    $setupIntent = StripeSetupIntent::fromStripeObject($stripeObject);

    expect($setupIntent->customer())->toBe("cus_nested");
});

test("can handle nested payment method object", function (): void {
    $stripeObject = Util::convertToStripeObject([
        "id" => "seti_123",
        "object" => "setup_intent",
        "customer" => "cus_123",
        "payment_method" => [
            "id" => "pm_nested",
            "object" => "payment_method",
            "type" => "card",
        ],
        "status" => "succeeded",
    ], null);

    $setupIntent = StripeSetupIntent::fromStripeObject($stripeObject);

    expect($setupIntent->paymentMethod())->toBe("pm_nested");
});

test("can handle last setup error", function (): void {
    $stripeObject = Util::convertToStripeObject([
        "id" => "seti_123",
        "object" => "setup_intent",
        "customer" => "cus_123",
        "status" => "requires_payment_method",
        "last_setup_error" => [
            "type" => "card_error",
            "code" => "card_declined",
            "message" => "Your card was declined.",
        ],
    ], null);

    $setupIntent = StripeSetupIntent::fromStripeObject($stripeObject);

    expect($setupIntent->lastSetupError())
        ->toBeArray()
        ->and($setupIntent->lastSetupError()["type"])->toBe("card_error")
        ->and($setupIntent->lastSetupError()["code"])->toBe("card_declined");
});

test("converts payment method types to values in toArray", function (): void {
    $setupIntent = StripeSetupIntent::make()
        ->withCustomer("cus_123")
        ->withPaymentMethodTypes(collect([PaymentMethodType::Card, PaymentMethodType::Klarna]));

    $array = $setupIntent->toArray();

    expect($array["payment_method_types"])->toBeArray()
        ->and($array["payment_method_types"])->toBe(["card", "klarna"]);
});

test("can set metadata", function (): void {
    $setupIntent = StripeSetupIntent::make()
        ->withCustomer("cus_123")
        ->withMetadata(["user_id" => "123", "plan" => "premium"]);

    expect($setupIntent->metadata())->toBe(["user_id" => "123", "plan" => "premium"]);
});

test("handles all setup intent statuses", function (): void {
    $statuses = [
        "requires_payment_method" => SetupIntentStatus::RequiresPaymentMethod,
        "requires_confirmation" => SetupIntentStatus::RequiresConfirmation,
        "requires_action" => SetupIntentStatus::RequiresAction,
        "processing" => SetupIntentStatus::Processing,
        "canceled" => SetupIntentStatus::Canceled,
        "succeeded" => SetupIntentStatus::Succeeded,
    ];

    foreach ($statuses as $statusString => $statusEnum) {
        $stripeObject = Util::convertToStripeObject([
            "id" => "seti_123",
            "object" => "setup_intent",
            "customer" => "cus_123",
            "status" => $statusString,
        ], null);

        $setupIntent = StripeSetupIntent::fromStripeObject($stripeObject);

        expect($setupIntent->status())->toBe($statusEnum);
    }
});

test("handles both usage types", function (): void {
    $usageTypes = [
        "on_session" => SetupIntentUsage::OnSession,
        "off_session" => SetupIntentUsage::OffSession,
    ];

    foreach ($usageTypes as $usageString => $usageEnum) {
        $stripeObject = Util::convertToStripeObject([
            "id" => "seti_123",
            "object" => "setup_intent",
            "customer" => "cus_123",
            "usage" => $usageString,
        ], null);

        $setupIntent = StripeSetupIntent::fromStripeObject($stripeObject);

        expect($setupIntent->usage())->toBe($usageEnum);
    }
});

test("can create setup intent with minimal properties", function (): void {
    $setupIntent = StripeSetupIntent::make()
        ->withCustomer("cus_123");

    $array = $setupIntent->toArray();

    expect($array)->toHaveKey("customer")
        ->and($array["customer"])->toBe("cus_123")
        ->and(count($array))->toBe(1);
});
