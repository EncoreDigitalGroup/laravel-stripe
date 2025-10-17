<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Support\Building\Builders;

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\Stripe\Enums\SubscriptionScheduleProrationBehavior;
use EncoreDigitalGroup\Stripe\Objects\Subscription\Schedules\StripeSubscriptionSchedulePhase;
use Illuminate\Support\Collection;

class SubscriptionSchedulePhaseBuilder
{
    public function build(
        ?CarbonImmutable $startDate = null,
        ?CarbonImmutable $endDate = null,
        ?Collection $items = null,
        ?int $iterations = null,
        ?SubscriptionScheduleProrationBehavior $prorationBehavior = null,
        ?int $trialPeriodDays = null,
        ?CarbonImmutable $trialEnd = null,
        ?string $defaultPaymentMethod = null,
        ?Collection $defaultTaxRates = null,
        ?string $collectionMethod = null,
        ?string $invoiceSettings = null,
        ?array $metadata = null,
    ): StripeSubscriptionSchedulePhase {
        return StripeSubscriptionSchedulePhase::make(
            startDate: $startDate,
            endDate: $endDate,
            items: $items,
            iterations: $iterations,
            prorationBehavior: $prorationBehavior,
            trialPeriodDays: $trialPeriodDays,
            trialEnd: $trialEnd,
            defaultPaymentMethod: $defaultPaymentMethod,
            defaultTaxRates: $defaultTaxRates,
            collectionMethod: $collectionMethod,
            invoiceSettings: $invoiceSettings,
            metadata: $metadata,
        );
    }
}