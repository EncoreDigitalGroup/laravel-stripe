<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Subscription;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Enums\CollectionMethod;
use EncoreDigitalGroup\Stripe\Enums\ProrationBehavior;
use EncoreDigitalGroup\Stripe\Enums\SubscriptionStatus;
use EncoreDigitalGroup\Stripe\Objects\Subscription\Schedules\StripeSubscriptionSchedule;
use EncoreDigitalGroup\Stripe\Services\StripeSubscriptionService;
use EncoreDigitalGroup\Stripe\Support\HasTimestamps;
use PHPGenesis\Common\Traits\HasMake;
use Stripe\Subscription;

class StripeSubscription
{
    use HasMake;
    use HasTimestamps;

    private ?string $id = null;
    private ?string $customer = null;
    private ?SubscriptionStatus $status = null;
    private ?CarbonImmutable $currentPeriodStart = null;
    private ?CarbonImmutable $currentPeriodEnd = null;
    private ?CarbonImmutable $cancelAt = null;
    private ?CarbonImmutable $canceledAt = null;
    private ?CarbonImmutable $trialStart = null;
    private ?CarbonImmutable $trialEnd = null;
    private ?array $items = null;
    private ?string $defaultPaymentMethod = null;
    private ?array $metadata = null;
    private ?string $currency = null;
    private ?CollectionMethod $collectionMethod = null;
    private ?StripeBillingCycleAnchorConfig $billingCycleAnchorConfig = null;
    private ?ProrationBehavior $prorationBehavior = null;
    private ?bool $cancelAtPeriodEnd = null;
    private ?int $daysUntilDue = null;
    private ?string $description = null;
    private ?StripeSubscriptionSchedule $subscriptionSchedule = null;

    /**
     * Create a StripeSubscription instance from a Stripe API Subscription object
     */
    public static function fromStripeObject(Subscription $stripeSubscription): self
    {
        $items = self::extractItems($stripeSubscription);
        $customer = self::extractCustomerId($stripeSubscription->customer);
        $status = self::extractStatus($stripeSubscription->status);
        $defaultPaymentMethod = self::extractPaymentMethodId($stripeSubscription->default_payment_method ?? null);
        $collectionMethod = self::extractCollectionMethod($stripeSubscription->collection_method ?? null);
        $billingCycleAnchorConfig = self::extractBillingCycleAnchorConfig($stripeSubscription);
        $prorationBehavior = self::extractProrationBehavior($stripeSubscription->proration_behavior ?? null);

        $instance = self::make();

        if ($stripeSubscription->id) {
            $instance = $instance->withId($stripeSubscription->id);
        }
        if ($customer !== "" && $customer !== "0") {
            $instance = $instance->withCustomer($customer);
        }
        if ($status instanceof SubscriptionStatus) {
            $instance = $instance->withStatus($status);
        }
        if ($stripeSubscription->current_period_start ?? null) {
            $instance = $instance->withCurrentPeriodStart(self::timestampToCarbon($stripeSubscription->current_period_start));
        }
        if ($stripeSubscription->current_period_end ?? null) {
            $instance = $instance->withCurrentPeriodEnd(self::timestampToCarbon($stripeSubscription->current_period_end));
        }
        if ($stripeSubscription->cancel_at ?? null) {
            $instance = $instance->withCancelAt(self::timestampToCarbon($stripeSubscription->cancel_at));
        }
        if ($stripeSubscription->canceled_at ?? null) {
            $instance = $instance->withCanceledAt(self::timestampToCarbon($stripeSubscription->canceled_at));
        }
        if ($stripeSubscription->trial_start ?? null) {
            $instance = $instance->withTrialStart(self::timestampToCarbon($stripeSubscription->trial_start));
        }
        if ($stripeSubscription->trial_end ?? null) {
            $instance = $instance->withTrialEnd(self::timestampToCarbon($stripeSubscription->trial_end));
        }
        if ($items !== null && $items !== []) {
            $instance = $instance->withItems($items);
        }
        if ($defaultPaymentMethod !== null && $defaultPaymentMethod !== "" && $defaultPaymentMethod !== "0") {
            $instance = $instance->withDefaultPaymentMethod($defaultPaymentMethod);
        }
        if ($stripeSubscription->metadata) {
            $instance = $instance->withMetadata($stripeSubscription->metadata->toArray());
        }
        if ($stripeSubscription->currency ?? null) {
            $instance = $instance->withCurrency($stripeSubscription->currency);
        }
        if ($collectionMethod instanceof CollectionMethod) {
            $instance = $instance->withCollectionMethod($collectionMethod);
        }
        if ($billingCycleAnchorConfig instanceof StripeBillingCycleAnchorConfig) {
            $instance = $instance->withBillingCycleAnchorConfig($billingCycleAnchorConfig);
        }
        if ($prorationBehavior instanceof ProrationBehavior) {
            $instance = $instance->withProrationBehavior($prorationBehavior);
        }
        if (isset($stripeSubscription->cancel_at_period_end)) {
            $instance = $instance->withCancelAtPeriodEnd($stripeSubscription->cancel_at_period_end);
        }
        if ($stripeSubscription->days_until_due ?? null) {
            $instance = $instance->withDaysUntilDue($stripeSubscription->days_until_due);
        }
        if ($stripeSubscription->description ?? null) {
            return $instance->withDescription($stripeSubscription->description);
        }

        return $instance;
    }

    private static function extractItems(Subscription $stripeSubscription): ?array
    {
        if (!$stripeSubscription->items->data) {
            return null;
        }

        $items = [];
        foreach ($stripeSubscription->items->data as $item) {
            $items[] = [
                "id" => $item->id,
                "price" => $item->price->id ?? null,
                "quantity" => $item->quantity,
                "metadata" => $item->metadata->toArray(),
            ];
        }

        return $items;
    }

    private static function extractCustomerId(mixed $customer): string
    {
        if (is_string($customer)) {
            return $customer;
        }

        return $customer->id;
    }

    private static function extractStatus(?string $status): ?SubscriptionStatus
    {
        if ($status === null) {
            return null;
        }

        return SubscriptionStatus::from($status);
    }

    private static function extractPaymentMethodId(mixed $paymentMethod): ?string
    {
        if ($paymentMethod === null) {
            return null;
        }

        if (is_string($paymentMethod)) {
            return $paymentMethod;
        }

        return $paymentMethod->id;
    }

    private static function extractCollectionMethod(?string $collectionMethod): ?CollectionMethod
    {
        if ($collectionMethod === null) {
            return null;
        }

        return CollectionMethod::from($collectionMethod);
    }

    private static function extractBillingCycleAnchorConfig(Subscription $stripeSubscription): ?StripeBillingCycleAnchorConfig
    {
        $config = $stripeSubscription->billing_cycle_anchor_config ?? null;

        if ($config === null) {
            return null;
        }

        $instance = StripeBillingCycleAnchorConfig::make();

        if (isset($config->day_of_month)) {
            $instance = $instance->withDayOfMonth($config->day_of_month);
        }
        if (isset($config->month)) {
            $instance = $instance->withMonth($config->month);
        }
        if (isset($config->hour)) {
            $instance = $instance->withHour($config->hour);
        }
        if (isset($config->minute)) {
            $instance = $instance->withMinute($config->minute);
        }
        if (isset($config->second)) {
            return $instance->withSecond($config->second);
        }

        return $instance;
    }

    private static function extractProrationBehavior(?string $prorationBehavior): ?ProrationBehavior
    {
        if ($prorationBehavior === null) {
            return null;
        }

        return ProrationBehavior::from($prorationBehavior);
    }

    public function issueFirstInvoiceOn(CarbonInterface $date): self
    {
        $this->billingCycleAnchorConfig = StripeBillingCycleAnchorConfig::make()
            ->withDayOfMonth($date->day)
            ->withMonth($date->month)
            ->withHour($date->hour)
            ->withMinute($date->minute)
            ->withSecond($date->second);

        return $this;
    }

    public function toArray(): array
    {
        $array = [
            "id" => $this->id,
            "customer" => $this->customer,
            "status" => $this->status?->value,
            "current_period_start" => self::carbonToTimestamp($this->currentPeriodStart),
            "current_period_end" => self::carbonToTimestamp($this->currentPeriodEnd),
            "cancel_at" => self::carbonToTimestamp($this->cancelAt),
            "canceled_at" => self::carbonToTimestamp($this->canceledAt),
            "trial_start" => self::carbonToTimestamp($this->trialStart),
            "trial_end" => self::carbonToTimestamp($this->trialEnd),
            "items" => $this->items,
            "default_payment_method" => $this->defaultPaymentMethod,
            "metadata" => $this->metadata,
            "currency" => $this->currency,
            "collection_method" => $this->collectionMethod?->value,
            "billing_cycle_anchor_config" => $this->billingCycleAnchorConfig?->toArray(),
            "proration_behavior" => $this->prorationBehavior?->value,
            "cancel_at_period_end" => $this->cancelAtPeriodEnd,
            "days_until_due" => $this->daysUntilDue,
            "description" => $this->description,
        ];

        return Arr::whereNotNull($array);
    }

    public function get(string $subscriptionId): self
    {
        $service = app(StripeSubscriptionService::class);

        return $service->get($subscriptionId);
    }

    public function save(): self
    {
        $service = app(StripeSubscriptionService::class);

        $result = is_null($this->id) ? $service->create($this) : $service->update($this->id, $this);

        // Save schedule changes if the schedule was accessed
        if ($this->subscriptionSchedule instanceof \EncoreDigitalGroup\Stripe\Objects\Subscription\Schedules\StripeSubscriptionSchedule) {
            $savedSchedule = $this->subscriptionSchedule->save();
            // Update the result's schedule cache with the saved schedule
            $result->subscriptionSchedule = $savedSchedule;
        }

        return $result;
    }

    public function schedule(): StripeSubscriptionSchedule
    {
        if (!$this->subscriptionSchedule instanceof \EncoreDigitalGroup\Stripe\Objects\Subscription\Schedules\StripeSubscriptionSchedule) {
            $this->subscriptionSchedule = StripeSubscriptionSchedule::make();
        }

        $this->subscriptionSchedule->setParentSubscription($this);

        return $this->subscriptionSchedule;
    }

    // Fluent setters
    public function withId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function withCustomer(string $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function withStatus(SubscriptionStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function withCurrentPeriodStart(CarbonImmutable $currentPeriodStart): self
    {
        $this->currentPeriodStart = $currentPeriodStart;

        return $this;
    }

    public function withCurrentPeriodEnd(CarbonImmutable $currentPeriodEnd): self
    {
        $this->currentPeriodEnd = $currentPeriodEnd;

        return $this;
    }

    public function withCancelAt(CarbonImmutable $cancelAt): self
    {
        $this->cancelAt = $cancelAt;

        return $this;
    }

    public function withCanceledAt(CarbonImmutable $canceledAt): self
    {
        $this->canceledAt = $canceledAt;

        return $this;
    }

    public function withTrialStart(CarbonImmutable $trialStart): self
    {
        $this->trialStart = $trialStart;

        return $this;
    }

    public function withTrialEnd(CarbonImmutable $trialEnd): self
    {
        $this->trialEnd = $trialEnd;

        return $this;
    }

    public function withItems(array $items): self
    {
        $this->items = $items;

        return $this;
    }

    public function withDefaultPaymentMethod(string $defaultPaymentMethod): self
    {
        $this->defaultPaymentMethod = $defaultPaymentMethod;

        return $this;
    }

    public function withMetadata(array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function withCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function withCollectionMethod(CollectionMethod $collectionMethod): self
    {
        $this->collectionMethod = $collectionMethod;

        return $this;
    }

    public function withBillingCycleAnchorConfig(StripeBillingCycleAnchorConfig $billingCycleAnchorConfig): self
    {
        $this->billingCycleAnchorConfig = $billingCycleAnchorConfig;

        return $this;
    }

    public function withProrationBehavior(ProrationBehavior $prorationBehavior): self
    {
        $this->prorationBehavior = $prorationBehavior;

        return $this;
    }

    public function withCancelAtPeriodEnd(bool $cancelAtPeriodEnd): self
    {
        $this->cancelAtPeriodEnd = $cancelAtPeriodEnd;

        return $this;
    }

    public function withDaysUntilDue(int $daysUntilDue): self
    {
        $this->daysUntilDue = $daysUntilDue;

        return $this;
    }

    public function withDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    // Getter methods
    public function id(): ?string
    {
        return $this->id;
    }

    public function customer(): ?string
    {
        return $this->customer;
    }

    public function status(): ?SubscriptionStatus
    {
        return $this->status;
    }

    public function currentPeriodStart(): ?CarbonImmutable
    {
        return $this->currentPeriodStart;
    }

    public function currentPeriodEnd(): ?CarbonImmutable
    {
        return $this->currentPeriodEnd;
    }

    public function cancelAt(): ?CarbonImmutable
    {
        return $this->cancelAt;
    }

    public function canceledAt(): ?CarbonImmutable
    {
        return $this->canceledAt;
    }

    public function trialStart(): ?CarbonImmutable
    {
        return $this->trialStart;
    }

    public function trialEnd(): ?CarbonImmutable
    {
        return $this->trialEnd;
    }

    public function items(): ?array
    {
        return $this->items;
    }

    public function defaultPaymentMethod(): ?string
    {
        return $this->defaultPaymentMethod;
    }

    public function metadata(): ?array
    {
        return $this->metadata;
    }

    public function currency(): ?string
    {
        return $this->currency;
    }

    public function collectionMethod(): ?CollectionMethod
    {
        return $this->collectionMethod;
    }

    public function billingCycleAnchorConfig(): ?StripeBillingCycleAnchorConfig
    {
        return $this->billingCycleAnchorConfig;
    }

    public function prorationBehavior(): ?ProrationBehavior
    {
        return $this->prorationBehavior;
    }

    public function cancelAtPeriodEnd(): ?bool
    {
        return $this->cancelAtPeriodEnd;
    }

    public function daysUntilDue(): ?int
    {
        return $this->daysUntilDue;
    }

    public function description(): ?string
    {
        return $this->description;
    }
}