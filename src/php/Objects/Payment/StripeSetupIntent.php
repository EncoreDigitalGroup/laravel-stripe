<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Payment;

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Enums\PaymentMethodType;
use EncoreDigitalGroup\Stripe\Enums\SetupIntentStatus;
use EncoreDigitalGroup\Stripe\Enums\SetupIntentUsage;
use EncoreDigitalGroup\Stripe\Services\StripeSetupIntentService;
use EncoreDigitalGroup\Stripe\Support\Traits\HasGet;
use EncoreDigitalGroup\Stripe\Support\Traits\HasMetadata;
use EncoreDigitalGroup\Stripe\Support\Traits\HasSave;
use EncoreDigitalGroup\Stripe\Support\Traits\HasTimestamps;
use Illuminate\Support\Collection;
use PHPGenesis\Common\Traits\HasMake;
use Stripe\SetupIntent;

class StripeSetupIntent
{
    use HasGet;
    use HasMake;
    use HasMetadata;
    use HasSave;
    use HasTimestamps;

    private ?string $id = null;
    private ?string $customer = null;
    private ?string $description = null;
    private ?string $paymentMethod = null;
    private ?SetupIntentStatus $status = null;
    private ?SetupIntentUsage $usage = null;
    private ?CarbonImmutable $created = null;
    private ?string $clientSecret = null;
    private ?array $lastSetupError = null;

    /** @var ?Collection<PaymentMethodType> */
    private ?Collection $paymentMethodTypes = null;

    public static function fromStripeObject(SetupIntent $setupIntent): self
    {
        $instance = self::make();

        $instance = self::setBasicProperties($instance, $setupIntent);
        $instance = self::setEnumProperties($instance, $setupIntent);
        $instance = self::setRelationProperties($instance, $setupIntent);

        return self::setAdditionalProperties($instance, $setupIntent);
    }

    private static function setBasicProperties(self $instance, SetupIntent $setupIntent): self
    {
        if ($setupIntent->id) {
            $instance = $instance->withId($setupIntent->id);
        }

        if ($setupIntent->description ?? null) {
            $instance = $instance->withDescription($setupIntent->description);
        }

        if ($setupIntent->client_secret ?? null) {
            return $instance->withClientSecret($setupIntent->client_secret);
        }

        return $instance;
    }

    private static function setEnumProperties(self $instance, SetupIntent $setupIntent): self
    {
        if ($setupIntent->status ?? null) {
            $instance = $instance->withStatus(SetupIntentStatus::from($setupIntent->status));
        }

        if ($setupIntent->usage ?? null) {
            return $instance->withUsage(SetupIntentUsage::from($setupIntent->usage));
        }

        return $instance;
    }

    private static function setRelationProperties(self $instance, SetupIntent $setupIntent): self
    {
        if ($setupIntent->customer ?? null) {
            $customerId = is_string($setupIntent->customer) ? $setupIntent->customer : $setupIntent->customer->id;
            $instance = $instance->withCustomer($customerId);
        }

        if ($setupIntent->payment_method ?? null) {
            $paymentMethodId = is_string($setupIntent->payment_method) ? $setupIntent->payment_method : $setupIntent->payment_method->id;
            $instance = $instance->withPaymentMethod($paymentMethodId);
        }

        return $instance;
    }

    private static function setAdditionalProperties(self $instance, SetupIntent $setupIntent): self
    {
        if ($setupIntent->created ?? null) {
            $created = self::timestampToCarbon($setupIntent->created);
            if ($created instanceof CarbonImmutable) {
                $instance = $instance->withCreated($created);
            }
        }

        if (isset($setupIntent->last_setup_error)) {
            $errorJson = json_encode($setupIntent->last_setup_error);
            $lastSetupError = $errorJson !== false ? json_decode($errorJson, true) : null;
            $instance = $instance->withLastSetupError($lastSetupError);
        }

        if (isset($setupIntent->payment_method_types)) {
            $paymentMethodTypes = collect($setupIntent->payment_method_types)
                ->map(fn (string $type): PaymentMethodType => PaymentMethodType::from($type));
            $instance = $instance->withPaymentMethodTypes($paymentMethodTypes);
        }

        if (isset($setupIntent->metadata)) {
            return $instance->withMetadata(self::extractMetadata($setupIntent));
        }

        return $instance;
    }

    public function toArray(): array
    {
        $array = [
            "id" => $this->id,
            "customer" => $this->customer,
            "description" => $this->description,
            "payment_method" => $this->paymentMethod,
            "status" => $this->status?->value,
            "usage" => $this->usage?->value,
            "created" => self::carbonToTimestamp($this->created),
            "client_secret" => $this->clientSecret,
            "last_setup_error" => $this->lastSetupError,
            "payment_method_types" => $this->paymentMethodTypes?->map(fn (PaymentMethodType $type): string => $type->value)?->toArray(),
            "metadata" => $this->metadata,
        ];

        return Arr::whereNotNull($array);
    }

    public function service(): StripeSetupIntentService
    {
        return app(StripeSetupIntentService::class);
    }

    public function withId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function id(): ?string
    {
        return $this->id;
    }

    public function withCustomer(string $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function customer(): ?string
    {
        return $this->customer;
    }

    public function withDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function withPaymentMethod(string $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function paymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function withStatus(SetupIntentStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function status(): ?SetupIntentStatus
    {
        return $this->status;
    }

    public function withUsage(SetupIntentUsage $usage): self
    {
        $this->usage = $usage;

        return $this;
    }

    public function usage(): ?SetupIntentUsage
    {
        return $this->usage;
    }

    public function withCreated(CarbonImmutable $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function created(): ?CarbonImmutable
    {
        return $this->created;
    }

    public function withClientSecret(string $clientSecret): self
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    public function clientSecret(): ?string
    {
        return $this->clientSecret;
    }

    public function withLastSetupError(?array $lastSetupError): self
    {
        $this->lastSetupError = $lastSetupError;

        return $this;
    }

    public function lastSetupError(): ?array
    {
        return $this->lastSetupError;
    }

    /**
     * @param  Collection<int, PaymentMethodType>  $paymentMethodTypes
     */
    public function withPaymentMethodTypes(Collection $paymentMethodTypes): self
    {
        $this->paymentMethodTypes = $paymentMethodTypes;

        return $this;
    }

    /**
     * @return ?Collection<PaymentMethodType>
     */
    public function paymentMethodTypes(): ?Collection
    {
        return $this->paymentMethodTypes;
    }
}