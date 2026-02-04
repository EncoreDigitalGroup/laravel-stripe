<?php



use EncoreDigitalGroup\Stripe\Support\StripeWebhookHelper;
use Illuminate\Http\Request;
use Stripe\Event as StripeEvent;
use Stripe\Exception\SignatureVerificationException;

describe("StripeWebhookHelper", function (): void {
    test("getSignatureHeader returns stripe signature from request", function (): void {
        $request = Request::create("/webhook", "POST", [], [], [], [
            "HTTP_STRIPE_SIGNATURE" => "t=1234567890,v1=signature_value",
        ]);

        app()->instance("request", $request);

        $signature = StripeWebhookHelper::getSignatureHeader();

        expect($signature)->toBe("t=1234567890,v1=signature_value");
    });

    test("getSignatureHeader returns empty string when header not present", function (): void {
        $request = Request::create("/webhook", "POST");

        app()->instance("request", $request);

        $signature = StripeWebhookHelper::getSignatureHeader();

        expect($signature)->toBe("");
    });

    test("constructEvent throws exception for invalid signature", function (): void {
        $payload = "{\"id\": \"evt_test\", \"object\": \"event\"}";
        $signature = "invalid_signature";
        $secret = "whsec_test";

        expect(fn (): StripeEvent => StripeWebhookHelper::constructEvent($payload, $signature, $secret))
            ->toThrow(SignatureVerificationException::class);
    });

    test("constructEvent returns StripeEvent for valid signature", function (): void {
        $payload = "{\"id\": \"evt_test\", \"object\": \"event\", \"type\": \"customer.created\", \"data\": {\"object\": {}}}";
        $timestamp = time();
        $secret = "whsec_test";

        // Generate valid signature
        $signedPayload = "{$timestamp}.{$payload}";
        $signature = hash_hmac("sha256", $signedPayload, $secret);
        $fullSignature = "t={$timestamp},v1={$signature}";

        $event = StripeWebhookHelper::constructEvent($payload, $fullSignature, $secret);

        expect($event)->toBeInstanceOf(StripeEvent::class);
    });
});
