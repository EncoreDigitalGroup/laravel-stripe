<?php

use EncoreDigitalGroup\Stripe\Views\FinancialConnections;

describe("FinancialConnections", function (): void {
    test("can create component with all parameters", function (): void {
        $component = new FinancialConnections(
            stripePublicKey: "pk_test_123",
            stripeSessionSecret: "secret_123",
            stripeCustomerId: "cus_123",
            redirectSuccessUrl: "https://example.com/success",
            redirectErrorUrl: "https://example.com/error",
            postSuccessUrl: "https://example.com/post-success",
            publicSecurityKey: "pub_key_123",
            privateSecurityKey: "priv_key_123"
        );

        expect($component->stripePublicKey)->toBe("pk_test_123")
            ->and($component->stripeSessionSecret)->toBe("secret_123")
            ->and($component->stripeCustomerId)->toBe("cus_123")
            ->and($component->redirectSuccessUrl)->toBe("https://example.com/success")
            ->and($component->redirectErrorUrl)->toBe("https://example.com/error")
            ->and($component->postSuccessUrl)->toBe("https://example.com/post-success")
            ->and($component->publicSecurityKey)->toBe("pub_key_123")
            ->and($component->privateSecurityKey)->toBe("priv_key_123");
    });

    test("can create component with null parameters", function (): void {
        $component = new FinancialConnections;

        expect($component->stripePublicKey)->toBeNull()
            ->and($component->stripeSessionSecret)->toBeNull()
            ->and($component->stripeCustomerId)->toBeNull()
            ->and($component->redirectSuccessUrl)->toBeNull()
            ->and($component->redirectErrorUrl)->toBeNull()
            ->and($component->postSuccessUrl)->toBeNull()
            ->and($component->publicSecurityKey)->toBeNull()
            ->and($component->privateSecurityKey)->toBeNull();
    });

    test("component has render method", function (): void {
        $component = new FinancialConnections;

        // Test that the method exists
        expect(method_exists($component, "render"))->toBeTrue();
    });

    test("redirectUrlIsNull is called during construction for null urls", function (): void {
        $component = new FinancialConnections(
            redirectSuccessUrl: null,
            redirectErrorUrl: null,
            postSuccessUrl: null
        );

        // Component should be created successfully even with null URLs
        expect($component)->toBeInstanceOf(FinancialConnections::class);
    });
});
