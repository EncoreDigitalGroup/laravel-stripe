<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Product;

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Enums\BillingScheme;
use EncoreDigitalGroup\Stripe\Enums\PriceType;
use EncoreDigitalGroup\Stripe\Enums\RecurringAggregateUsage;
use EncoreDigitalGroup\Stripe\Enums\RecurringInterval;
use EncoreDigitalGroup\Stripe\Enums\RecurringUsageType;
use EncoreDigitalGroup\Stripe\Enums\TaxBehavior;
use EncoreDigitalGroup\Stripe\Enums\TiersMode;
use EncoreDigitalGroup\Stripe\Support\Building\StripeBuilder;
use EncoreDigitalGroup\Stripe\Support\HasMake;
use EncoreDigitalGroup\Stripe\Support\HasTimestamps;
use Stripe\Price;
use Stripe\StripeObject;

class StripePrice
{
    use HasMake;
    use HasTimestamps;

    public function __construct(
        public ?string $id = null,
        public ?string $product = null,
        public ?bool $active = null,
        public ?string $currency = null,
        public ?int $unitAmount = null,
        public ?string $unitAmountDecimal = null,
        public ?PriceType $type = null,
        public ?BillingScheme $billingScheme = null,
        public ?StripeRecurring $recurring = null,
        public ?string $nickname = null,
        public ?array $metadata = null,
        public ?string $lookupKey = null,
        public ?StripeProductTierCollection $tiers = null,
        public ?TiersMode $tiersMode = null,
        public ?int $transformQuantity = null,
        public ?StripeCustomUnitAmount $customUnitAmount = null,
        public ?TaxBehavior $taxBehavior = null,
        public ?CarbonImmutable $created = null
    ) {}

    public static function make(mixed ...$params): static
    {
        // Handle tiers parameter conversion from array to collection
        if (isset($params['tiers']) && is_array($params['tiers']) && !($params['tiers'] instanceof StripeProductTierCollection)) {
            $params['tiers'] = new StripeProductTierCollection($params['tiers']);
        }

        // Handle customUnitAmount parameter conversion from array to DTO
        if (isset($params['customUnitAmount']) && is_array($params['customUnitAmount']) && !($params['customUnitAmount'] instanceof StripeCustomUnitAmount)) {
            $params['customUnitAmount'] = (new StripeBuilder())->customUnitAmount()->build(...$params['customUnitAmount']);
        }

        return new static(...$params);
    }

    public static function fromStripeObject(Price $stripePrice): self
    {
        $recurring = self::extractRecurring($stripePrice);
        $tiers = self::extractTiers($stripePrice);
        $customUnitAmount = self::extractCustomUnitAmount($stripePrice);
        $product = is_string($stripePrice->product) ? $stripePrice->product : $stripePrice->product->id;

        $type = null;
        if (isset($stripePrice->type)) {
            $type = PriceType::from($stripePrice->type);
        }

        $billingScheme = null;
        if (isset($stripePrice->billing_scheme)) {
            $billingScheme = BillingScheme::from($stripePrice->billing_scheme);
        }

        $tiersMode = null;
        if (isset($stripePrice->tiers_mode)) {
            $tiersMode = TiersMode::from($stripePrice->tiers_mode);
        }

        $taxBehavior = null;
        if (isset($stripePrice->tax_behavior)) {
            $taxBehavior = TaxBehavior::from($stripePrice->tax_behavior);
        }

        return self::make(
            id: $stripePrice->id,
            product: $product,
            active: $stripePrice->active ?? null,
            currency: $stripePrice->currency,
            unitAmount: $stripePrice->unit_amount ?? null,
            unitAmountDecimal: $stripePrice->unit_amount_decimal ?? null,
            type: $type,
            billingScheme: $billingScheme,
            recurring: $recurring,
            nickname: $stripePrice->nickname ?? null,
            metadata: $stripePrice->metadata->toArray(),
            lookupKey: $stripePrice->lookup_key ?? null,
            tiers: $tiers,
            tiersMode: $tiersMode,
            transformQuantity: $stripePrice->transform_quantity ?? null,
            customUnitAmount: $customUnitAmount,
            taxBehavior: $taxBehavior,
            created: self::timestampToCarbon($stripePrice->created ?? null)
        );
    }

    private static function extractRecurring(Price $stripePrice): ?StripeRecurring
    {
        if (!isset($stripePrice->recurring)) {
            return null;
        }

        /** @var StripeObject $recurringObj */
        $recurringObj = $stripePrice->recurring;

        $interval = null;
        if (isset($recurringObj->interval)) {
            $interval = RecurringInterval::from($recurringObj->interval);
        }

        $usageType = null;
        if (isset($recurringObj->usage_type)) {
            $usageType = RecurringUsageType::from($recurringObj->usage_type);
        }

        $aggregateUsage = null;
        if (isset($recurringObj->aggregate_usage)) {
            $aggregateUsage = RecurringAggregateUsage::from($recurringObj->aggregate_usage);
        }

        return (new StripeBuilder())->recurring()->build(
            interval: $interval,
            intervalCount: $recurringObj->interval_count ?? null,
            trialPeriodDays: $recurringObj->trial_period_days ?? null,
            usageType: $usageType,
            aggregateUsage: $aggregateUsage
        );
    }

    private static function extractTiers(Price $stripePrice): ?StripeProductTierCollection
    {
        if (!isset($stripePrice->tiers)) {
            return null;
        }

        $tiers = [];
        foreach ($stripePrice->tiers as $tier) {
            /** @var StripeObject $tierObj */
            $tierObj = $tier;
            $tiers[] = (new StripeBuilder())->tier()->build(
                upTo: $tierObj->up_to ?? null,
                unitAmount: $tierObj->unit_amount ?? null,
                unitAmountDecimal: $tierObj->unit_amount_decimal ?? null,
                flatAmount: $tierObj->flat_amount ?? null,
                flatAmountDecimal: $tierObj->flat_amount_decimal ?? null
            );
        }

        return new StripeProductTierCollection($tiers);
    }

    private static function extractCustomUnitAmount(Price $stripePrice): ?StripeCustomUnitAmount
    {
        if (!isset($stripePrice->custom_unit_amount)) {
            return null;
        }

        /** @var StripeObject $customUnitAmountObj */
        $customUnitAmountObj = $stripePrice->custom_unit_amount;

        return (new StripeBuilder())->customUnitAmount()->build(
            minimum: $customUnitAmountObj->minimum ?? null,
            maximum: $customUnitAmountObj->maximum ?? null,
            preset: $customUnitAmountObj->preset ?? null
        );
    }

    public function toArray(): array
    {
        $array = [
            "id" => $this->id,
            "product" => $this->product,
            "active" => $this->active,
            "currency" => $this->currency,
            "unit_amount" => $this->unitAmount,
            "unit_amount_decimal" => $this->unitAmountDecimal,
            "type" => $this->type?->value,
            "billing_scheme" => $this->billingScheme?->value,
            "recurring" => $this->recurring?->toArray(),
            "nickname" => $this->nickname,
            "metadata" => $this->metadata,
            "lookup_key" => $this->lookupKey,
            "tiers" => $this->tiers?->toArray(),
            "tiers_mode" => $this->tiersMode?->value,
            "transform_quantity" => $this->transformQuantity,
            "custom_unit_amount" => $this->customUnitAmount?->toArray(),
            "tax_behavior" => $this->taxBehavior?->value,
            "created" => self::carbonToTimestamp($this->created),
        ];

        return Arr::whereNotNull($array);
    }


}