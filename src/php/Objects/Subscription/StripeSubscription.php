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
use EncoreDigitalGroup\Stripe\Services\StripeSubscriptionScheduleService;
use EncoreDigitalGroup\Stripe\Services\StripeSubscriptionService;
use EncoreDigitalGroup\Stripe\Support\Traits\HasGet;
use EncoreDigitalGroup\Stripe\Support\Traits\HasTimestamps;
use Illuminate\Support\Collection;
use PHPGenesis\Common\Traits\HasMake;
use Stripe\Subscription;

class StripeSubscription
{
    use HasGet;
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

    /** @var ?Collection<StripeSubscriptionItem> */
    private ?Collection $items = null;

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
    private ?string $subscriptionScheduleId = null;

    /**
     * Create a StripeSubscription instance from a Stripe API Subscription object
     *
     * @phpstan-ignore complexity.functionLike
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

        $currentPeriodStart = self::timestampToCarbon($stripeSubscription->current_period_start ?? null);
        if ($currentPeriodStart instanceof CarbonImmutable) {
            $instance = $instance->withCurrentPeriodStart($currentPeriodStart);
        }

        $currentPeriodEnd = self::timestampToCarbon($stripeSubscription->current_period_end ?? null);
        if ($currentPeriodEnd instanceof CarbonImmutable) {
            $instance = $instance->withCurrentPeriodEnd($currentPeriodEnd);
        }

        $cancelAt = self::timestampToCarbon($stripeSubscription->cancel_at ?? null);
        if ($cancelAt instanceof CarbonImmutable) {
            $instance = $instance->withCancelAt($cancelAt);
        }

        $canceledAt = self::timestampToCarbon($stripeSubscription->canceled_at ?? null);
        if ($canceledAt instanceof CarbonImmutable) {
            $instance = $instance->withCanceledAt($canceledAt);
        }

        $trialStart = self::timestampToCarbon($stripeSubscription->trial_start ?? null);
        if ($trialStart instanceof CarbonImmutable) {
            $instance = $instance->withTrialStart($trialStart);
        }

        $trialEnd = self::timestampToCarbon($stripeSubscription->trial_end ?? null);
        if ($trialEnd instanceof CarbonImmutable) {
            $instance = $instance->withTrialEnd($trialEnd);
        }

        if ($items instanceof Collection && $items->isNotEmpty()) {
            $instance = $instance->withItems($items);
        }

        if ($defaultPaymentMethod !== null && $defaultPaymentMethod !== "" && $defaultPaymentMethod !== "0") {
            $instance = $instance->withDefaultPaymentMethod($defaultPaymentMethod);
        }

        if (isset($stripeSubscription->metadata)) {
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
            $instance->withDescription($stripeSubscription->description);
        }

        if ($stripeSubscription->schedule ?? null) {
            $instance->subscriptionScheduleId = $stripeSubscription->schedule;
        }

        return $instance;
    }

    private static function extractItems(Subscription $stripeSubscription): ?Collection
    {
        if (!$stripeSubscription->items->data) {
            return null;
        }

        $items = [];
        foreach ($stripeSubscription->items->data as $item) {
            $subscriptionItem = StripeSubscriptionItem::make()->withId($item->id);

            if (!is_null($item->quantity)) {
                $subscriptionItem = $subscriptionItem->withQuantity($item->quantity);
            }

            if (isset($item->price->id)) {
                $subscriptionItem = $subscriptionItem->withPrice($item->price->id);
            }

            $currentPeriodStart = self::timestampToCarbon($item->current_period_start ?? null);
            if ($currentPeriodStart instanceof CarbonImmutable) {
                $subscriptionItem = $subscriptionItem->withCurrentPeriodStart($currentPeriodStart);
            }

            $currentPeriodEnd = self::timestampToCarbon($item->current_period_end ?? null);
            if ($currentPeriodEnd instanceof CarbonImmutable) {
                $subscriptionItem = $subscriptionItem->withCurrentPeriodEnd($currentPeriodEnd);
            }

            if (isset($item->metadata)) {
                $subscriptionItem = $subscriptionItem->withMetadata($item->metadata->toArray());
            }

            $items[] = $subscriptionItem;
        }

        return collect($items);
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

    public function service(): StripeSubscriptionService
    {
        return app(StripeSubscriptionService::class);
    }

    public function toArray(): array
    {
        $items = null;
        if ($this->items instanceof Collection) {
            $items = $this->items->map(fn(StripeSubscriptionItem $item): array => $item->toArray())->all();
        }

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
            "items" => $items,
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

    /** This is custom as we are saving multiple objects which the HasSave trait does not cover. */
    public function save(): self
    {
        $service = app(StripeSubscriptionService::class);

        $result = is_null($this->id) ? $service->create($this) : $service->update($this->id, $this);

        // Save schedule changes if the schedule was accessed
        if ($this->subscriptionSchedule instanceof StripeSubscriptionSchedule) {
            $savedSchedule = $this->subscriptionSchedule->save();
            $result->subscriptionSchedule = $savedSchedule;
        }

        return $result;
    }

    public function schedule(bool $refresh = false): ?StripeSubscriptionSchedule
    {
        if ($this->subscriptionSchedule instanceof StripeSubscriptionSchedule && !$refresh) {
            return $this->subscriptionSchedule;
        }

        if ($this->subscriptionScheduleId !== null && $this->subscriptionScheduleId !== "" && $this->subscriptionScheduleId !== "0") {
            $this->subscriptionSchedule = app(StripeSubscriptionScheduleService::class)->get($this->subscriptionScheduleId);
            $this->subscriptionSchedule->setParentSubscription($this);
            return $this->subscriptionSchedule;
        }

        return null;
    }

    public function scheduleId(): ?string
    {
        return $this->subscriptionScheduleId;
    }

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

    /** @deprecated Use StripeSubscriptionItem::withCurrentPeriodStart() instead */
    public function withCurrentPeriodStart(CarbonImmutable $currentPeriodStart): self
    {
        $this->currentPeriodStart = $currentPeriodStart;

        return $this;
    }

    /** @deprecated Use StripeSubscriptionItem::withCurrentPeriodEnd() instead */
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

    public function withItems(Collection $items): self
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

    public function items(): ?Collection
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