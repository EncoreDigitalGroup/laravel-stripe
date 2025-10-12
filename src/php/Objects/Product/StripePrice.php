<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe\Objects\Product;

use EncoreDigitalGroup\Common\Stripe\Enums\BillingScheme;
use EncoreDigitalGroup\Common\Stripe\Enums\PriceType;
use EncoreDigitalGroup\Common\Stripe\Enums\RecurringAggregateUsage;
use EncoreDigitalGroup\Common\Stripe\Enums\RecurringInterval;
use EncoreDigitalGroup\Common\Stripe\Enums\RecurringUsageType;
use EncoreDigitalGroup\Common\Stripe\Enums\TaxBehavior;
use EncoreDigitalGroup\Common\Stripe\Enums\TiersMode;
use EncoreDigitalGroup\Common\Stripe\Support\HasMake;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use Stripe\Price;

class StripePrice
{
    use HasMake;

    public function __construct(
        public ?string $id = null,
        public ?string $product = null,
        public ?bool $active = null,
        public ?string $currency = null,
        public ?int $unitAmount = null,
        public ?string $unitAmountDecimal = null,
        public ?PriceType $type = null,
        public ?BillingScheme $billingScheme = null,
        public ?array $recurring = null,
        public ?string $nickname = null,
        public ?array $metadata = null,
        public ?string $lookupKey = null,
        public ?array $tiers = null,
        public ?TiersMode $tiersMode = null,
        public ?int $transformQuantity = null,
        public ?array $customUnitAmount = null,
        public ?TaxBehavior $taxBehavior = null,
        public ?int $created = null
    ) {}

    /**
     * Create a StripePrice instance from a Stripe API Price object
     */
    public static function fromStripeObject(Price $stripePrice): self
    {
        $recurring = self::extractRecurring($stripePrice);
        $tiers = self::extractTiers($stripePrice);
        $customUnitAmount = self::extractCustomUnitAmount($stripePrice);

        return self::make(
            id: $stripePrice->id,
            product: is_string($stripePrice->product)
                ? $stripePrice->product
                : $stripePrice->product->id,
            active: $stripePrice->active,
            currency: $stripePrice->currency,
            unitAmount: $stripePrice->unit_amount,
            unitAmountDecimal: $stripePrice->unit_amount_decimal,
            type: $stripePrice->type ? PriceType::from($stripePrice->type) : null,
            billingScheme: $stripePrice->billing_scheme ? BillingScheme::from($stripePrice->billing_scheme) : null,
            recurring: $recurring,
            nickname: $stripePrice->nickname,
            metadata: $stripePrice->metadata->toArray(),
            lookupKey: $stripePrice->lookup_key,
            tiers: $tiers,
            tiersMode: $stripePrice->tiers_mode ? TiersMode::from($stripePrice->tiers_mode) : null,
            transformQuantity: $stripePrice->transform_quantity,
            customUnitAmount: $customUnitAmount,
            taxBehavior: $stripePrice->tax_behavior ? TaxBehavior::from($stripePrice->tax_behavior) : null,
            created: $stripePrice->created
        );
    }

    private static function extractRecurring(Price $stripePrice): ?array
    {
        if (!$stripePrice->recurring) {
            return null;
        }

        /** @var \Stripe\StripeObject $recurringObj */
        $recurringObj = $stripePrice->recurring;

        $interval = property_exists($recurringObj, "interval") && $recurringObj->interval !== null && $recurringObj->interval
            ? RecurringInterval::from($recurringObj->interval)
            : null;

        $usageType = property_exists($recurringObj, "usage_type") && $recurringObj->usage_type !== null && $recurringObj->usage_type
            ? RecurringUsageType::from($recurringObj->usage_type)
            : null;

        $aggregateUsage = property_exists($recurringObj, "aggregate_usage") && $recurringObj->aggregate_usage !== null && $recurringObj->aggregate_usage
            ? RecurringAggregateUsage::from($recurringObj->aggregate_usage)
            : null;

        return [
            "interval" => $interval,
            "interval_count" => $recurringObj->interval_count ?? null,
            "trial_period_days" => $recurringObj->trial_period_days ?? null,
            "usage_type" => $usageType,
            "aggregate_usage" => $aggregateUsage,
        ];
    }

    private static function extractTiers(Price $stripePrice): ?array
    {
        if (!$stripePrice->tiers) {
            return null;
        }

        $tiers = [];
        foreach ($stripePrice->tiers as $tier) {
            /** @var \Stripe\StripeObject $tierObj */
            $tierObj = $tier;
            $tiers[] = [
                "up_to" => $tierObj->up_to ?? null,
                "unit_amount" => $tierObj->unit_amount ?? null,
                "unit_amount_decimal" => $tierObj->unit_amount_decimal ?? null,
                "flat_amount" => $tierObj->flat_amount ?? null,
                "flat_amount_decimal" => $tierObj->flat_amount_decimal ?? null,
            ];
        }

        return $tiers;
    }

    private static function extractCustomUnitAmount(Price $stripePrice): ?array
    {
        if (!$stripePrice->custom_unit_amount) {
            return null;
        }

        /** @var \Stripe\StripeObject $customUnitAmountObj */
        $customUnitAmountObj = $stripePrice->custom_unit_amount;

        return [
            "maximum" => $customUnitAmountObj->maximum ?? null,
            "minimum" => $customUnitAmountObj->minimum ?? null,
            "preset" => $customUnitAmountObj->preset ?? null,
        ];
    }

    public function toArray(): array
    {
        // Convert recurring enums back to values if present
        $recurring = $this->recurring;
        if ($recurring !== null && $recurring !== []) {
            $recurring = [
                "interval" => isset($recurring["interval"]) && $recurring["interval"] instanceof RecurringInterval
                    ? $recurring["interval"]->value
                    : ($recurring["interval"] ?? null),
                "interval_count" => $recurring["interval_count"] ?? null,
                "trial_period_days" => $recurring["trial_period_days"] ?? null,
                "usage_type" => isset($recurring["usage_type"]) && $recurring["usage_type"] instanceof RecurringUsageType
                    ? $recurring["usage_type"]->value
                    : ($recurring["usage_type"] ?? null),
                "aggregate_usage" => isset($recurring["aggregate_usage"]) && $recurring["aggregate_usage"] instanceof RecurringAggregateUsage
                    ? $recurring["aggregate_usage"]->value
                    : ($recurring["aggregate_usage"] ?? null),
            ];
            $recurring = Arr::whereNotNull($recurring);
        }

        $array = [
            "id" => $this->id,
            "product" => $this->product,
            "active" => $this->active,
            "currency" => $this->currency,
            "unit_amount" => $this->unitAmount,
            "unit_amount_decimal" => $this->unitAmountDecimal,
            "type" => $this->type?->value,
            "billing_scheme" => $this->billingScheme?->value,
            "recurring" => $recurring,
            "nickname" => $this->nickname,
            "metadata" => $this->metadata,
            "lookup_key" => $this->lookupKey,
            "tiers" => $this->tiers,
            "tiers_mode" => $this->tiersMode?->value,
            "transform_quantity" => $this->transformQuantity,
            "custom_unit_amount" => $this->customUnitAmount,
            "tax_behavior" => $this->taxBehavior?->value,
        ];

        return Arr::whereNotNull($array);
    }
}