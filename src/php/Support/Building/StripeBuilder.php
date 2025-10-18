<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Support\Building;

use EncoreDigitalGroup\Stripe\Support\Building\Builders\AddressBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\BankAccountBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\CustomerBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\CustomUnitAmountBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\FinancialConnectionBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\PriceBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\ProductBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\RecurringBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\ShippingBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\TierBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\TransactionRefreshBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\WebhookBuilder;

class StripeBuilder
{
    #region Main Entity Builders

    public function customer(): CustomerBuilder
    {
        return new CustomerBuilder;
    }

    public function product(): ProductBuilder
    {
        return new ProductBuilder;
    }

    public function price(): PriceBuilder
    {
        return new PriceBuilder;
    }

    public function financialConnection(): FinancialConnectionBuilder
    {
        return new FinancialConnectionBuilder;
    }

    #endregion

    #region Support Object Builders

    public function address(): AddressBuilder
    {
        return new AddressBuilder;
    }

    public function shipping(): ShippingBuilder
    {
        return new ShippingBuilder;
    }

    public function webhook(): WebhookBuilder
    {
        return new WebhookBuilder;
    }

    #endregion

    #region Sub-Object Builders

    public function tier(): TierBuilder
    {
        return new TierBuilder;
    }

    public function customUnitAmount(): CustomUnitAmountBuilder
    {
        return new CustomUnitAmountBuilder;
    }

    public function recurring(): RecurringBuilder
    {
        return new RecurringBuilder;
    }

    public function bankAccount(): BankAccountBuilder
    {
        return new BankAccountBuilder;
    }

    public function transactionRefresh(): TransactionRefreshBuilder
    {
        return new TransactionRefreshBuilder;
    }
    #endregion
}