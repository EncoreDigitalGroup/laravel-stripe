<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Stripe\Objects\Customer\StripeCustomer;
use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeFinancialConnection;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\BankAccountBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\FinancialConnectionBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\TransactionRefreshBuilder;

describe("FinancialConnectionBuilder", function (): void {
    test("can build a basic financial connection", function (): void {
        $customer = StripeCustomer::make(email: "test@example.com");
        $builder = new FinancialConnectionBuilder();
        $connection = $builder->build(
            customer: $customer
        );

        expect($connection)
            ->toBeInstanceOf(StripeFinancialConnection::class)
            ->and($connection->customer)->toBe($customer)
            ->and($connection->permissions)->toBe(["transactions"]);
    });

    test("can build financial connection with all parameters", function (): void {
        $customer = StripeCustomer::make(email: "business@example.com", name: "Acme Corp");
        $builder = new FinancialConnectionBuilder();
        $connection = $builder->build(
            customer: $customer,
            permissions: ["payment_method", "balances"]
        );

        expect($connection)
            ->toBeInstanceOf(StripeFinancialConnection::class)
            ->and($connection->customer)->toBe($customer)
            ->and($connection->permissions)->toBe(["payment_method", "balances"]);
    });

    describe("Nested Builders", function (): void {
        test("can access bank account builder", function (): void {
            $builder = new FinancialConnectionBuilder();
            $bankAccountBuilder = $builder->bankAccount();

            expect($bankAccountBuilder)->toBeInstanceOf(BankAccountBuilder::class);
        });

        test("can access transaction refresh builder", function (): void {
            $builder = new FinancialConnectionBuilder();
            $transactionRefreshBuilder = $builder->transactionRefresh();

            expect($transactionRefreshBuilder)->toBeInstanceOf(TransactionRefreshBuilder::class);
        });
    });
});