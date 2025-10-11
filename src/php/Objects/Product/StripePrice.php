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
        $recurring = null;
        if ($stripePrice->recurring) {
            $recurring = [
                'interval' => $stripePrice->recurring->interval ? RecurringInterval::from($stripePrice->recurring->interval) : null,
                'interval_count' => $stripePrice->recurring->interval_count,
                'trial_period_days' => $stripePrice->recurring->trial_period_days,
                'usage_type' => $stripePrice->recurring->usage_type ? RecurringUsageType::from($stripePrice->recurring->usage_type) : null,
                'aggregate_usage' => $stripePrice->recurring->aggregate_usage ? RecurringAggregateUsage::from($stripePrice->recurring->aggregate_usage) : null,
            ];
        }

        $tiers = null;
        if ($stripePrice->tiers) {
            $tiers = [];
            foreach ($stripePrice->tiers as $tier) {
                $tiers[] = [
                    'up_to' => $tier->up_to,
                    'unit_amount' => $tier->unit_amount,
                    'unit_amount_decimal' => $tier->unit_amount_decimal,
                    'flat_amount' => $tier->flat_amount,
                    'flat_amount_decimal' => $tier->flat_amount_decimal,
                ];
            }
        }

        $customUnitAmount = null;
        if ($stripePrice->custom_unit_amount) {
            $customUnitAmount = [
                'maximum' => $stripePrice->custom_unit_amount->maximum,
                'minimum' => $stripePrice->custom_unit_amount->minimum,
                'preset' => $stripePrice->custom_unit_amount->preset,
            ];
        }

        return self::make(
            id: $stripePrice->id,
            product: is_string($stripePrice->product)
                ? $stripePrice->product
                : $stripePrice->product?->id,
            active: $stripePrice->active,
            currency: $stripePrice->currency,
            unitAmount: $stripePrice->unit_amount,
            unitAmountDecimal: $stripePrice->unit_amount_decimal,
            type: $stripePrice->type ? PriceType::from($stripePrice->type) : null,
            billingScheme: $stripePrice->billing_scheme ? BillingScheme::from($stripePrice->billing_scheme) : null,
            recurring: $recurring,
            nickname: $stripePrice->nickname,
            metadata: $stripePrice->metadata?->toArray(),
            lookupKey: $stripePrice->lookup_key,
            tiers: $tiers,
            tiersMode: $stripePrice->tiers_mode ? TiersMode::from($stripePrice->tiers_mode) : null,
            transformQuantity: $stripePrice->transform_quantity,
            customUnitAmount: $customUnitAmount,
            taxBehavior: $stripePrice->tax_behavior ? TaxBehavior::from($stripePrice->tax_behavior) : null,
            created: $stripePrice->created
        );
    }

    public function toArray(): array
    {
        // Convert recurring enums back to values if present
        $recurring = $this->recurring;
        if ($recurring) {
            $recurring = [
                'interval' => $recurring['interval']?->value ?? $recurring['interval'],
                'interval_count' => $recurring['interval_count'] ?? null,
                'trial_period_days' => $recurring['trial_period_days'] ?? null,
                'usage_type' => $recurring['usage_type']?->value ?? $recurring['usage_type'],
                'aggregate_usage' => $recurring['aggregate_usage']?->value ?? $recurring['aggregate_usage'],
            ];
            $recurring = Arr::whereNotNull($recurring);
        }

        $array = [
            'id' => $this->id,
            'product' => $this->product,
            'active' => $this->active,
            'currency' => $this->currency,
            'unit_amount' => $this->unitAmount,
            'unit_amount_decimal' => $this->unitAmountDecimal,
            'type' => $this->type?->value,
            'billing_scheme' => $this->billingScheme?->value,
            'recurring' => $recurring,
            'nickname' => $this->nickname,
            'metadata' => $this->metadata,
            'lookup_key' => $this->lookupKey,
            'tiers' => $this->tiers,
            'tiers_mode' => $this->tiersMode?->value,
            'transform_quantity' => $this->transformQuantity,
            'custom_unit_amount' => $this->customUnitAmount,
            'tax_behavior' => $this->taxBehavior?->value,
        ];

        return Arr::whereNotNull($array);
    }
}