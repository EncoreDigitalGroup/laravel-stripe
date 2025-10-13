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
use Stripe\StripeObject;

class StripePrice
{
    use HasMake;

    public function __construct(
        public ?string        $id = null,
        public ?string        $product = null,
        public ?bool          $active = null,
        public ?string        $currency = null,
        public ?int           $unitAmount = null,
        public ?string        $unitAmountDecimal = null,
        public ?PriceType     $type = null,
        public ?BillingScheme $billingScheme = null,
        public ?array         $recurring = null,
        public ?string        $nickname = null,
        public ?array         $metadata = null,
        public ?string        $lookupKey = null,
        public ?array         $tiers = null,
        public ?TiersMode     $tiersMode = null,
        public ?int           $transformQuantity = null,
        public ?array         $customUnitAmount = null,
        public ?TaxBehavior   $taxBehavior = null,
        public ?int           $created = null
    ) {}

    public static function fromStripeObject(Price $stripePrice): self
    {
        $recurring = self::extractRecurring($stripePrice);
        $tiers = self::extractTiers($stripePrice);
        $customUnitAmount = self::extractCustomUnitAmount($stripePrice);

        $product = null;
        if (is_string($stripePrice->product)) {
            $product = $stripePrice->product;
        } else {
            $product = $stripePrice->product->id;
        }

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
            created: $stripePrice->created ?? null
        );
    }

    private static function extractRecurring(Price $stripePrice): ?array
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
        if (!isset($stripePrice->tiers)) {
            return null;
        }

        $tiers = [];
        foreach ($stripePrice->tiers as $tier) {
            /** @var StripeObject $tierObj */
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
        if (!isset($stripePrice->custom_unit_amount)) {
            return null;
        }

        /** @var StripeObject $customUnitAmountObj */
        $customUnitAmountObj = $stripePrice->custom_unit_amount;

        return [
            "maximum" => $customUnitAmountObj->maximum ?? null,
            "minimum" => $customUnitAmountObj->minimum ?? null,
            "preset" => $customUnitAmountObj->preset ?? null,
        ];
    }

    public function toArray(): array
    {
        $recurring = $this->normalizeRecurring($this->recurring);

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

    private function normalizeRecurring(?array $recurring): ?array
    {
        if ($recurring === null || $recurring === []) {
            return $recurring;
        }

        $interval = $this->normalizeRecurringField(
            $recurring["interval"] ?? null,
            RecurringInterval::class
        );

        $usageType = $this->normalizeRecurringField(
            $recurring["usage_type"] ?? null,
            RecurringUsageType::class
        );

        $aggregateUsage = $this->normalizeRecurringField(
            $recurring["aggregate_usage"] ?? null,
            RecurringAggregateUsage::class
        );

        $normalized = [
            "interval" => $interval,
            "interval_count" => $recurring["interval_count"] ?? null,
            "trial_period_days" => $recurring["trial_period_days"] ?? null,
            "usage_type" => $usageType,
            "aggregate_usage" => $aggregateUsage,
        ];

        return Arr::whereNotNull($normalized);
    }

    private function normalizeRecurringField(mixed $field, string $enumClass): ?string
    {
        if ($field instanceof RecurringInterval || $field instanceof RecurringUsageType || $field instanceof RecurringAggregateUsage) {
            return $field->value;
        }

        return $field;
    }
}