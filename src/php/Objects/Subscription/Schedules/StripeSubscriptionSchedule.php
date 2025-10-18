<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Subscription\Schedules;

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Enums\SubscriptionScheduleEndBehavior;
use EncoreDigitalGroup\Stripe\Enums\SubscriptionScheduleStatus;
use EncoreDigitalGroup\Stripe\Objects\Subscription\StripeSubscription;
use EncoreDigitalGroup\Stripe\Services\StripeSubscriptionScheduleService;
use EncoreDigitalGroup\Stripe\Support\HasTimestamps;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use PHPGenesis\Common\Traits\HasMake;
use Stripe\StripeObject;

class StripeSubscriptionSchedule
{
    use HasMake;
    use HasTimestamps;

    private ?string $id = null;
    private ?string $object = null;
    private ?CarbonImmutable $canceledAt = null;
    private ?CarbonImmutable $completedAt = null;
    private ?CarbonImmutable $created = null;
    private ?string $customer = null;
    private ?StripeSubscriptionSchedulePhase $defaultSettings = null;
    private ?SubscriptionScheduleEndBehavior $endBehavior = null;
    private ?bool $livemode = null;
    private ?array $metadata = null;
    private ?Collection $phases = null;
    private ?CarbonImmutable $releasedAt = null;
    private ?string $releasedSubscription = null;
    private ?SubscriptionScheduleStatus $status = null;
    private ?string $subscription = null;
    private ?string $testClock = null;
    private ?StripeSubscription $parentSubscription = null;

    public static function fromStripeObject(StripeObject $obj): self
    {
        $phases = null;
        if (isset($obj->phases)) {
            $phases = collect($obj->phases)->map(function ($phase): StripeSubscriptionSchedulePhase {
                return StripeSubscriptionSchedulePhase::fromStripeObject($phase);
            });
        }

        $defaultSettings = null;
        if (isset($obj->default_settings)) {
            $defaultSettings = StripeSubscriptionSchedulePhase::fromStripeObject($obj->default_settings);
        }

        $status = null;
        if (isset($obj->status)) {
            $status = SubscriptionScheduleStatus::from($obj->status);
        }

        $endBehavior = null;
        if (isset($obj->end_behavior)) {
            $endBehavior = SubscriptionScheduleEndBehavior::from($obj->end_behavior);
        }

        $instance = self::make();

        if ($obj->id ?? null) {
            $instance->id = $obj->id;
        }
        if ($obj->object ?? null) {
            $instance->object = $obj->object;
        }
        if (isset($obj->canceled_at)) {
            $instance->canceledAt = self::timestampToCarbon($obj->canceled_at);
        }
        if (isset($obj->completed_at)) {
            $instance->completedAt = self::timestampToCarbon($obj->completed_at);
        }
        if (isset($obj->created)) {
            $instance->created = self::timestampToCarbon($obj->created);
        }
        $customer = is_string($obj->customer ?? null) ? $obj->customer : $obj->customer?->id;
        if ($customer) {
            $instance->customer = $customer;
        }
        if ($defaultSettings instanceof \EncoreDigitalGroup\Stripe\Objects\Subscription\Schedules\StripeSubscriptionSchedulePhase) {
            $instance->defaultSettings = $defaultSettings;
        }
        if ($endBehavior) {
            $instance->endBehavior = $endBehavior;
        }
        if ($obj->livemode ?? null) {
            $instance->livemode = $obj->livemode;
        }
        if (isset($obj->metadata)) {
            $instance->metadata = $obj->metadata->toArray();
        }
        if ($phases) {
            $instance->phases = $phases;
        }
        if (isset($obj->released_at)) {
            $instance->releasedAt = self::timestampToCarbon($obj->released_at);
        }
        $releasedSubscription = is_string($obj->released_subscription ?? null) ? $obj->released_subscription : $obj->released_subscription?->id;
        if ($releasedSubscription) {
            $instance->releasedSubscription = $releasedSubscription;
        }
        if ($status) {
            $instance->status = $status;
        }
        $subscription = is_string($obj->subscription ?? null) ? $obj->subscription : $obj->subscription?->id;
        if ($subscription) {
            $instance->subscription = $subscription;
        }
        if ($obj->test_clock ?? null) {
            $instance->testClock = $obj->test_clock;
        }

        return $instance;
    }

    public function toArray(): array
    {
        $array = [
            "id" => $this->id,
            "object" => $this->object,
            "canceled_at" => self::carbonToTimestamp($this->canceledAt),
            "completed_at" => self::carbonToTimestamp($this->completedAt),
            "created" => self::carbonToTimestamp($this->created),
            "customer" => $this->customer,
            "default_settings" => $this->defaultSettings?->toArray(),
            "end_behavior" => $this->endBehavior?->value,
            "livemode" => $this->livemode,
            "metadata" => $this->metadata,
            "phases" => $this->phases?->map(fn ($phase) => $phase->toArray())->toArray(),
            "released_at" => self::carbonToTimestamp($this->releasedAt),
            "released_subscription" => $this->releasedSubscription,
            "status" => $this->status?->value,
            "subscription" => $this->subscription,
            "test_clock" => $this->testClock,
        ];

        return Arr::whereNotNull($array);
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

    public function withSubscription(string $subscription): self
    {
        $this->subscription = $subscription;

        return $this;
    }

    public function withEndBehavior(SubscriptionScheduleEndBehavior $endBehavior): self
    {
        $this->endBehavior = $endBehavior;

        return $this;
    }

    public function withStatus(SubscriptionScheduleStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function withPhases(Collection $phases): self
    {
        $this->phases = $phases;

        return $this;
    }

    public function withMetadata(array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function withDefaultSettings(StripeSubscriptionSchedulePhase $defaultSettings): self
    {
        $this->defaultSettings = $defaultSettings;

        return $this;
    }

    public function withReleasedAt(CarbonImmutable $releasedAt): self
    {
        $this->releasedAt = $releasedAt;

        return $this;
    }

    public function withCanceledAt(CarbonImmutable $canceledAt): self
    {
        $this->canceledAt = $canceledAt;

        return $this;
    }

    public function withCompletedAt(CarbonImmutable $completedAt): self
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    public function id(): ?string
    {
        return $this->id;
    }

    public function object(): ?string
    {
        return $this->object;
    }

    public function canceledAt(): ?CarbonImmutable
    {
        return $this->canceledAt;
    }

    public function completedAt(): ?CarbonImmutable
    {
        return $this->completedAt;
    }

    public function created(): ?CarbonImmutable
    {
        return $this->created;
    }

    public function customer(): ?string
    {
        return $this->customer;
    }

    public function defaultSettings(): ?StripeSubscriptionSchedulePhase
    {
        return $this->defaultSettings;
    }

    public function endBehavior(): ?SubscriptionScheduleEndBehavior
    {
        return $this->endBehavior;
    }

    public function livemode(): ?bool
    {
        return $this->livemode;
    }

    public function metadata(): ?array
    {
        return $this->metadata;
    }

    public function phases(): ?Collection
    {
        return $this->phases;
    }

    public function releasedAt(): ?CarbonImmutable
    {
        return $this->releasedAt;
    }

    public function releasedSubscription(): ?string
    {
        return $this->releasedSubscription;
    }

    public function status(): ?SubscriptionScheduleStatus
    {
        return $this->status;
    }

    public function subscription(): ?string
    {
        return $this->subscription;
    }

    public function testClock(): ?string
    {
        return $this->testClock;
    }

    public function setParentSubscription(StripeSubscription $subscription): self
    {
        $this->parentSubscription = $subscription;

        return $this;
    }

    public function get(?string $subscriptionId = null): self
    {
        $scheduleService = app(StripeSubscriptionScheduleService::class);

        // Use provided subscriptionId, fall back to parent subscription, or use existing subscription property
        $targetSubscriptionId = $subscriptionId ?? $this->parentSubscription?->id() ?? $this->subscription;

        if ($targetSubscriptionId === null) {
            throw new InvalidArgumentException("Subscription ID is required to fetch schedule");
        }

        $schedule = $scheduleService->forSubscription($targetSubscriptionId);

        if ($schedule === null) {
            // No schedule exists yet, create new empty instance
            $newSchedule = self::make()
                ->withPhases(collect([]));

            if ($this->parentSubscription instanceof \EncoreDigitalGroup\Stripe\Objects\Subscription\StripeSubscription) {
                $customer = $this->parentSubscription->customer();
                $subId = $this->parentSubscription->id();
                if ($customer !== null) {
                    $newSchedule = $newSchedule->withCustomer($customer);
                }
                if ($subId !== null) {
                    $newSchedule = $newSchedule->withSubscription($subId);
                }
            } elseif ($subscriptionId !== null && $subscriptionId !== '' && $subscriptionId !== '0') {
                $newSchedule = $newSchedule->withSubscription($subscriptionId);
            }

            // Preserve parent subscription reference
            $newSchedule->parentSubscription = $this->parentSubscription;

            return $newSchedule;
        }

        // Preserve parent subscription reference
        $schedule->parentSubscription = $this->parentSubscription;

        return $schedule;
    }

    public function addPhase(StripePhaseItem $phaseItem): self
    {
        if (!$this->phases instanceof \Illuminate\Support\Collection) {
            $this->phases = collect([]);
        }

        // Convert existing phases to array format for consistency
        $phasesArray = [];
        foreach ($this->phases as $phase) {
            $phasesArray[] = $phase;
        }

        // Create new phase from the item
        $newPhase = StripeSubscriptionSchedulePhase::make()
            ->withItems(collect([$phaseItem]));

        $phasesArray[] = $newPhase;

        $this->phases = collect($phasesArray);

        return $this;
    }

    public function save(): self
    {
        $scheduleService = app(StripeSubscriptionScheduleService::class);

        if ($this->id !== null && $this->id !== '' && $this->id !== '0') {
            // Update existing schedule
            $result = $scheduleService->update($this->id, $this);
        } else {
            // Create new schedule
            $result = $scheduleService->create($this);
        }

        // Preserve the parent subscription reference
        $result->parentSubscription = $this->parentSubscription;

        return $result;
    }
}