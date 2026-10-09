<?php

use EncoreDigitalGroup\Stripe\Enums\FinancialConnectionsAccountStatus;
use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeBankAccount;
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeFixtures;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeMethod;
use Stripe\Exception\InvalidRequestException;

describe("StripeFinancialConnectionsAccountService", function (): void {
    test("retrieves a Financial Connections account with its status", function (): void {
        $fake = Stripe::fake([
            StripeMethod::FinancialConnectionsAccountsRetrieve->value => StripeFixtures::financialConnectionsAccount([
                "id" => "fca_test",
                "status" => "active",
            ]),
        ]);

        $result = Stripe::financialConnection()->account()->get("fca_test");

        expect($result)
            ->toBeInstanceOf(StripeBankAccount::class)
            ->and($result->id())->toBe("fca_test")
            ->and($result->status())->toBe(FinancialConnectionsAccountStatus::Active)
            ->and($fake)->toHaveCalledStripeMethod(StripeMethod::FinancialConnectionsAccountsRetrieve, []);
    });

    test("propagates a retrieval InvalidRequestException unchanged", function (): void {
        Stripe::fake([
            StripeMethod::FinancialConnectionsAccountsRetrieve->value => function (array $params): never {
                throw InvalidRequestException::factory("Account was not found", 404);
            },
        ]);

        expect(fn (): StripeBankAccount => Stripe::financialConnection()->account()->get("fca_missing"))
            ->toThrow(InvalidRequestException::class, "Account was not found");
    });

    test("subscribes an account to transaction refreshes", function (): void {
        $fake = Stripe::fake([
            StripeMethod::FinancialConnectionsAccountsSubscribe->value => StripeFixtures::financialConnectionsAccount([
                "id" => "fca_test",
                "status" => "active",
            ]),
        ]);

        $result = Stripe::financialConnection()
            ->account()
            ->withId("fca_test")
            ->subscribeToTransactions();

        expect($result)
            ->toBeInstanceOf(StripeBankAccount::class)
            ->and($result->id())->toBe("fca_test")
            ->and($result->status())->toBe(FinancialConnectionsAccountStatus::Active)
            ->and($fake)->toHaveCalledStripeMethod(
                StripeMethod::FinancialConnectionsAccountsSubscribe,
                ["features" => ["transactions"]]
            );
    });

    test("propagates a subscription InvalidRequestException unchanged", function (): void {
        Stripe::fake([
            StripeMethod::FinancialConnectionsAccountsSubscribe->value => function (array $params): never {
                throw InvalidRequestException::factory("Account is not active", 400);
            },
        ]);

        expect(fn (): StripeBankAccount => Stripe::financialConnection()
            ->account()
            ->withId("fca_inactive")
            ->subscribeToTransactions())
            ->toThrow(InvalidRequestException::class, "Account is not active");
    });

    test("refreshes an account's transaction data", function (): void {
        $fake = Stripe::fake([
            StripeMethod::FinancialConnectionsAccountsRefresh->value => StripeFixtures::financialConnectionsAccount([
                "id" => "fca_test",
                "status" => "active",
            ]),
        ]);

        $result = Stripe::financialConnection()
            ->account()
            ->withId("fca_test")
            ->refreshTransactions();

        expect($result)
            ->toBeInstanceOf(StripeBankAccount::class)
            ->and($result->id())->toBe("fca_test")
            ->and($result->status())->toBe(FinancialConnectionsAccountStatus::Active)
            ->and($fake)->toHaveCalledStripeMethod(
                StripeMethod::FinancialConnectionsAccountsRefresh,
                ["features" => ["transactions"]]
            );
    });

    test("propagates a refresh InvalidRequestException unchanged", function (): void {
        Stripe::fake([
            StripeMethod::FinancialConnectionsAccountsRefresh->value => function (array $params): never {
                throw InvalidRequestException::factory("Refresh is unavailable", 400);
            },
        ]);

        expect(fn (): StripeBankAccount => Stripe::financialConnection()
            ->account()
            ->withId("fca_test")
            ->refreshTransactions())
            ->toThrow(InvalidRequestException::class, "Refresh is unavailable");
    });
});
