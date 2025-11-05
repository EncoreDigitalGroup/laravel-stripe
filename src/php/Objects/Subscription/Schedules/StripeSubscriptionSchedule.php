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
use EncoreDigitalGroup\Stripe\Support\Traits\HasReadOnlyFields;
use EncoreDigitalGroup\Stripe\Support\Traits\HasTimestamps;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use PHPGenesis\Common\Traits\HasMake;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeObject;
use Stripe\SubscriptionSchedule as StripeApiSubscriptionSchedule;

class StripeSubscriptionSchedule
{
    use HasReadOnlyFields;
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

    /** @phpstan-ignore complexity.functionLike */
    public static function fromStripeObject(StripeApiSubscriptionSchedule $obj): self
    {
        $phases = null;
        if (isset($obj->phases)) {
            /** @phpstan-ignore argument.templateType */
            $phases = collect($obj->phases)->map(function (StripeObject $phase): StripeSubscriptionSchedulePhase {
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
        if (isset($obj->customer)) {
            $instance->customer = is_string($obj->customer) ? $obj->customer : $obj->customer->id;
        }
        if ($defaultSettings instanceof StripeSubscriptionSchedulePhase) {
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
        if (isset($obj->released_subscription)) {
            $instance->releasedSubscription = $obj->released_subscription;
        }
        if ($status) {
            $instance->status = $status;
        }
        if (isset($obj->subscription)) {
            $instance->subscription = is_string($obj->subscription) ? $obj->subscription : $obj->subscription->id;
        }
        if ($obj->test_clock ?? null) {
            $instance->testClock = $obj->test_clock;
        }

        return $instance;
    }

    public function service(): StripeSubscriptionScheduleService
    {
        return app(StripeSubscriptionScheduleService::class);
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

    protected function getReadOnlyFields(): array
    {
        return ["id", "object", "created", "canceled_at", "completed_at", "released_at", "status"];
    }

    protected function getUpdateOnlyReadOnlyFields(): array
    {
        return ["customer", "subscription", "released_subscription"];
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

    /**
     * @throws ContainerExceptionInterface
     * @throws ApiErrorException
     * @throws NotFoundExceptionInterface
     */
    public function get(?string $id = null): self
    {
        $scheduleId = $id ?? $this->id;

        if ($scheduleId === null || $scheduleId === "" || $scheduleId === "0") {
            throw new InvalidArgumentException("Schedule ID is required to retrieve schedule");
        }

        $scheduleService = app(StripeSubscriptionScheduleService::class);

        return $scheduleService->get($scheduleId);
    }

    /** @throws ApiErrorException */
    public function create(): self
    {
        if (!$this->parentSubscription instanceof StripeSubscription || $this->parentSubscription->id() === null) {
            throw new InvalidArgumentException("Cannot create schedule: parent subscription must have an ID");
        }

        $result = $this->service()->fromSubscription($this->parentSubscription->id());
        $result->parentSubscription = $this->parentSubscription;

        return $result;
    }

    public function addPhase(StripeSubscriptionSchedulePhase $phase): self
    {
        $this->phases = ($this->phases ?? collect())->push($phase);

        return $this;
    }

    /** @throws ApiErrorException */
    public function save(): self
    {
        $scheduleService = app(StripeSubscriptionScheduleService::class);

        if ($this->id !== null && $this->id !== "" && $this->id !== "0") {
            $result = $scheduleService->update($this->id, $this);
        } else {
            $result = $scheduleService->create($this);
        }

        $result->parentSubscription = $this->parentSubscription;

        return $result;
    }
}