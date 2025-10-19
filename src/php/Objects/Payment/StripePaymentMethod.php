<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Payment;

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Enums\PaymentMethodType;
use EncoreDigitalGroup\Stripe\Objects\Support\StripeAddress;
use EncoreDigitalGroup\Stripe\Support\Traits\HasIdentifier;
use EncoreDigitalGroup\Stripe\Support\Traits\HasMetadata;
use EncoreDigitalGroup\Stripe\Support\Traits\HasTimestamps;
use Illuminate\Support\Collection;
use PHPGenesis\Common\Traits\HasMake;
use Stripe\PaymentMethod;
use Stripe\StripeObject;

class StripePaymentMethod
{
    use HasIdentifier;
    use HasMake;
    use HasMetadata;
    use HasTimestamps;

    private ?PaymentMethodType $type = null;
    private ?string $customer = null;
    private ?CarbonImmutable $created = null;
    private ?StripeAddress $billingDetails = null;

    /** @var ?Collection<string, mixed> */
    private ?Collection $card = null;

    /** @var ?Collection<string, mixed> */
    private ?Collection $usBankAccount = null;

    public static function fromStripeObject(PaymentMethod $paymentMethod): self
    {
        $instance = self::make();

        $instance = self::setBasicProperties($instance, $paymentMethod);
        $instance = self::setPaymentDetails($instance, $paymentMethod);

        return $instance;
    }

    private static function setBasicProperties(self $instance, PaymentMethod $paymentMethod): self
    {
        if ($paymentMethod->id) {
            $instance = $instance->withId($paymentMethod->id);
        }

        if ($paymentMethod->type ?? null) {
            $instance = $instance->withType(PaymentMethodType::from($paymentMethod->type));
        }

        if ($paymentMethod->customer ?? null) {
            $customerId = is_string($paymentMethod->customer) ? $paymentMethod->customer : $paymentMethod->customer->id;
            $instance = $instance->withCustomer($customerId);
        }

        if ($paymentMethod->created ?? null) {
            $created = self::timestampToCarbon($paymentMethod->created);
            if ($created instanceof CarbonImmutable) {
                $instance = $instance->withCreated($created);
            }
        }

        $instance = self::extractBillingDetails($instance, $paymentMethod);

        return $instance;
    }

    private static function extractBillingDetails(self $instance, PaymentMethod $paymentMethod): self
    {
        if (!isset($paymentMethod->billing_details)) {
            return $instance;
        }

        /** @var StripeObject $billingDetailsObj */
        $billingDetailsObj = $paymentMethod->billing_details;
        if (isset($billingDetailsObj->address)) {
            /** @var StripeObject $addressObj */
            $addressObj = $billingDetailsObj->address;

            return $instance->withBillingDetails(StripeAddress::fromStripeObject($addressObj));
        }

        return $instance;
    }

    /**
     * JSON encode/decode is used because Stripe's StripeObject uses magic methods for property access,
     * making it difficult to reliably convert to arrays via casting or reflection. JSON serialization
     * is the officially supported method to extract all data including nested objects.
     */
    private static function setPaymentDetails(self $instance, PaymentMethod $paymentMethod): self
    {
        if (isset($paymentMethod->card)) {
            $cardJson = json_encode($paymentMethod->card);
            /** @var ?array<string, mixed> $cardArray */
            $cardArray = $cardJson !== false ? json_decode($cardJson, true) : null;
            if (is_array($cardArray)) {
                $instance = $instance->withCard(collect($cardArray));
            }
        }

        if (isset($paymentMethod->us_bank_account)) {
            $usBankAccountJson = json_encode($paymentMethod->us_bank_account);
            /** @var ?array<string, mixed> $usBankAccountArray */
            $usBankAccountArray = $usBankAccountJson !== false ? json_decode($usBankAccountJson, true) : null;
            if (is_array($usBankAccountArray)) {
                $instance = $instance->withUsBankAccount(collect($usBankAccountArray));
            }
        }

        if (isset($paymentMethod->metadata)) {
            return $instance->withMetadata(self::extractMetadata($paymentMethod));
        }

        return $instance;
    }

    public function toArray(): array
    {
        $array = [
            "id" => $this->id,
            "type" => $this->type?->value,
            "customer" => $this->customer,
            "created" => self::carbonToTimestamp($this->created),
            "billing_details" => $this->billingDetails instanceof \EncoreDigitalGroup\Stripe\Objects\Support\StripeAddress ? [
                "address" => $this->billingDetails->toArray(),
            ] : null,
            "card" => $this->card?->toArray(),
            "us_bank_account" => $this->usBankAccount?->toArray(),
            "metadata" => $this->metadata,
        ];

        return Arr::whereNotNull($array);
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

    public function withType(PaymentMethodType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function type(): ?PaymentMethodType
    {
        return $this->type;
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

    public function withCreated(CarbonImmutable $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function created(): ?CarbonImmutable
    {
        return $this->created;
    }

    public function withBillingDetails(StripeAddress $billingDetails): self
    {
        $this->billingDetails = $billingDetails;

        return $this;
    }

    public function billingDetails(): ?StripeAddress
    {
        return $this->billingDetails;
    }

    /**
     * @param  Collection<string, mixed>  $card
     */
    public function withCard(Collection $card): self
    {
        $this->card = $card;

        return $this;
    }

    /**
     * @return ?Collection<string, mixed>
     */
    public function card(): ?Collection
    {
        return $this->card;
    }

    /**
     * @param  Collection<string, mixed>  $usBankAccount
     */
    public function withUsBankAccount(Collection $usBankAccount): self
    {
        $this->usBankAccount = $usBankAccount;

        return $this;
    }

    /**
     * @return ?Collection<string, mixed>
     */
    public function usBankAccount(): ?Collection
    {
        return $this->usBankAccount;
    }
}
