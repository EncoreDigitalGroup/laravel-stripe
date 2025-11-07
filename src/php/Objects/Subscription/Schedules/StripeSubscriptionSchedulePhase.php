<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Subscription\Schedules;

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Enums\SubscriptionScheduleProrationBehavior;
use EncoreDigitalGroup\Stripe\Support\Traits\HasTimestamps;
use Illuminate\Support\Collection;
use PHPGenesis\Common\Traits\HasMake;
use Stripe\StripeObject;

class StripeSubscriptionSchedulePhase
{
    use HasMake;
    use HasTimestamps;

    private ?CarbonImmutable $startDate = null;
    private ?CarbonImmutable $endDate = null;
    private ?Collection $items = null;
    private ?int $iterations = null;
    private ?SubscriptionScheduleProrationBehavior $prorationBehavior = null;
    private ?int $trialPeriodDays = null;
    private ?CarbonImmutable $trialEnd = null;
    private ?string $defaultPaymentMethod = null;
    private ?Collection $defaultTaxRates = null;
    private ?string $collectionMethod = null;
    private ?array $metadata = null;

    /** @phpstan-ignore complexity.functionLike */
    public static function fromStripeObject(StripeObject $obj): self
    {
        $items = null;

        if (isset($obj->items)) {
            $itemsData = isset($obj->items->data) ? $obj->items->data : (is_array($obj->items) ? $obj->items : null);

            if ($itemsData) {
                /** @phpstan-ignore-next-line argument.templateType */
                $items = collect($itemsData)->map(function ($item): StripePhaseItem {
                    $priceId = is_string($item->price) ? $item->price : ($item->price->id ?? null);

                    if (is_null($priceId) && isset($item->plan)) {
                        $priceId = is_string($item->plan) ? $item->plan : ($item->plan->id ?? null);
                    }

                    assert(!is_null($priceId), "price id must not be null");

                    $phaseItem = StripePhaseItem::make()
                        ->withPrice($priceId)
                        ->withQuantity($item->quantity ?? 1);

                    if (isset($item->metadata)) {
                        $metadata = is_array($item->metadata) ? $item->metadata : $item->metadata->toArray();
                        $phaseItem->withMetadata($metadata);
                    }

                    return $phaseItem;
                });
            }
        }

        $defaultTaxRates = null;
        if (isset($obj->default_tax_rates)) {
            /** @phpstan-ignore-next-line  argument.templateType */
            $defaultTaxRates = collect($obj->default_tax_rates);
        }

        $prorationBehavior = null;
        if (isset($obj->proration_behavior)) {
            $prorationBehavior = SubscriptionScheduleProrationBehavior::from($obj->proration_behavior);
        }

        $instance = self::make();

        if (isset($obj->start_date)) {
            $instance->startDate = self::timestampToCarbon($obj->start_date);
        }
        if (isset($obj->end_date)) {
            $instance->endDate = self::timestampToCarbon($obj->end_date);
        }
        if ($items) {
            $instance->items = $items;
        }
        if ($obj->iterations ?? null) {
            $instance->iterations = $obj->iterations;
        }
        if ($prorationBehavior) {
            $instance->prorationBehavior = $prorationBehavior;
        }
        if ($obj->trial_period_days ?? null) {
            $instance->trialPeriodDays = $obj->trial_period_days;
        }
        if (isset($obj->trial_end)) {
            $instance->trialEnd = self::timestampToCarbon($obj->trial_end);
        }
        if ($obj->default_payment_method ?? null) {
            $instance->defaultPaymentMethod = $obj->default_payment_method;
        }
        if ($defaultTaxRates instanceof Collection) {
            $instance->defaultTaxRates = $defaultTaxRates;
        }
        if ($obj->collection_method ?? null) {
            $instance->collectionMethod = $obj->collection_method;
        }
        if (isset($obj->metadata)) {
            $instance->metadata = $obj->metadata->toArray();
        }

        return $instance;
    }

    public function toArray(): array
    {
        $items = null;
        if ($this->items instanceof Collection) {
            $items = $this->items->map(function ($item) {
                if ($item instanceof StripePhaseItem) {
                    return $item->toArray();
                }

                return $item;
            })->toArray();
        }

        $array = [
            "start_date" => self::carbonToTimestamp($this->startDate),
            "end_date" => self::carbonToTimestamp($this->endDate),
            "items" => $items,
            "iterations" => $this->iterations,
            "proration_behavior" => $this->prorationBehavior?->value,
            "trial_period_days" => $this->trialPeriodDays,
            "trial_end" => self::carbonToTimestamp($this->trialEnd),
            "default_payment_method" => $this->defaultPaymentMethod,
            "default_tax_rates" => $this->defaultTaxRates?->toArray(),
            "collection_method" => $this->collectionMethod,
            "metadata" => $this->metadata,
        ];

        return Arr::whereNotNull($array);
    }

    // Fluent setters
    public function withStartDate(CarbonImmutable $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function withEndDate(CarbonImmutable $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function withItems(Collection $items): self
    {
        $this->items = $items;

        return $this;
    }

    public function withIterations(int $iterations): self
    {
        $this->iterations = $iterations;

        return $this;
    }

    public function withProrationBehavior(SubscriptionScheduleProrationBehavior $prorationBehavior): self
    {
        $this->prorationBehavior = $prorationBehavior;

        return $this;
    }

    public function withTrialPeriodDays(int $trialPeriodDays): self
    {
        $this->trialPeriodDays = $trialPeriodDays;

        return $this;
    }

    public function withTrialEnd(CarbonImmutable $trialEnd): self
    {
        $this->trialEnd = $trialEnd;

        return $this;
    }

    public function withDefaultPaymentMethod(string $defaultPaymentMethod): self
    {
        $this->defaultPaymentMethod = $defaultPaymentMethod;

        return $this;
    }

    public function withDefaultTaxRates(Collection $defaultTaxRates): self
    {
        $this->defaultTaxRates = $defaultTaxRates;

        return $this;
    }

    public function withCollectionMethod(string $collectionMethod): self
    {
        $this->collectionMethod = $collectionMethod;

        return $this;
    }

    public function withMetadata(array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function startDate(): ?CarbonImmutable
    {
        return $this->startDate;
    }

    public function endDate(): ?CarbonImmutable
    {
        return $this->endDate;
    }

    public function items(): ?Collection
    {
        return $this->items;
    }

    public function iterations(): ?int
    {
        return $this->iterations;
    }

    public function prorationBehavior(): ?SubscriptionScheduleProrationBehavior
    {
        return $this->prorationBehavior;
    }

    public function trialPeriodDays(): ?int
    {
        return $this->trialPeriodDays;
    }

    public function trialEnd(): ?CarbonImmutable
    {
        return $this->trialEnd;
    }

    public function defaultPaymentMethod(): ?string
    {
        return $this->defaultPaymentMethod;
    }

    public function defaultTaxRates(): ?Collection
    {
        return $this->defaultTaxRates;
    }

    public function collectionMethod(): ?string
    {
        return $this->collectionMethod;
    }

    public function metadata(): ?array
    {
        return $this->metadata;
    }
}