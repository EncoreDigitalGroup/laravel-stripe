<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Customer;

use EncoreDigitalGroup\StdLib\Exceptions\NullExceptions\ClassPropertyNullException;
use EncoreDigitalGroup\StdLib\Exceptions\NullExceptions\VariableNullException;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Enums\PaymentMethodType;
use EncoreDigitalGroup\Stripe\Objects\Payment\StripePaymentMethod;
use EncoreDigitalGroup\Stripe\Objects\Subscription\StripeSubscription;
use EncoreDigitalGroup\Stripe\Objects\Support\StripeAddress;
use EncoreDigitalGroup\Stripe\Services\StripeCustomerService;
use EncoreDigitalGroup\Stripe\Services\StripePaymentMethodService;
use EncoreDigitalGroup\Stripe\Services\StripeSubscriptionService;
use EncoreDigitalGroup\Stripe\Support\Traits\HasGet;
use EncoreDigitalGroup\Stripe\Support\Traits\HasSave;
use Illuminate\Support\Collection;
use PHPGenesis\Common\Traits\HasMake;
use Stripe\Customer;
use Stripe\StripeObject;

class StripeCustomer
{
    use HasGet;
    use HasMake;
    use HasSave;

    private ?string $id = null;
    private ?StripeAddress $address = null;
    private ?string $description = null;
    private ?string $email = null;
    private ?string $name = null;
    private ?string $phone = null;
    private ?StripeShipping $shipping = null;

    /** @var ?Collection<StripeSubscription> */
    private ?Collection $subscriptions = null;

    /** @var ?Collection<StripePaymentMethod> */
    private ?Collection $paymentMethods = null;

    /**
     * Create a StripeCustomer instance from a Stripe API Customer object
     */
    public static function fromStripeObject(Customer $stripeCustomer): self
    {
        $instance = self::make();

        if ($stripeCustomer->id) {
            $instance = $instance->withId($stripeCustomer->id);
        }

        if (isset($stripeCustomer->address)) {
            /** @var StripeObject $stripeAddress */
            $stripeAddress = $stripeCustomer->address;
            $instance = $instance->withAddress(StripeAddress::fromStripeObject($stripeAddress));
        }

        if ($stripeCustomer->description ?? null) {
            $instance = $instance->withDescription($stripeCustomer->description);
        }

        if ($stripeCustomer->email ?? null) {
            $instance = $instance->withEmail($stripeCustomer->email);
        }

        if ($stripeCustomer->name ?? null) {
            $instance = $instance->withName($stripeCustomer->name);
        }

        if ($stripeCustomer->phone ?? null) {
            $instance = $instance->withPhone($stripeCustomer->phone);
        }

        if (isset($stripeCustomer->shipping)) {
            /** @var StripeObject $stripeShipping */
            $stripeShipping = $stripeCustomer->shipping;
            $shipping = self::extractShipping($stripeShipping);

            if ($shipping instanceof StripeShipping) {
                $instance = $instance->withShipping($shipping);
            }
        }

        return $instance;
    }

    private static function extractShipping(StripeObject $stripeShipping): ?StripeShipping
    {
        $shippingAddress = null;
        if (isset($stripeShipping->address)) {
            /** @var StripeObject $shippingAddressObj */
            $shippingAddressObj = $stripeShipping->address;
            $shippingAddress = StripeAddress::fromStripeObject($shippingAddressObj);
        }

        // Only create shipping if we have the required fields (address and name)
        if (!$shippingAddress instanceof StripeAddress || !isset($stripeShipping->name)) {
            return null;
        }

        $shipping = StripeShipping::make()
            ->withAddress($shippingAddress)
            ->withName($stripeShipping->name);

        if ($stripeShipping->phone ?? null) {
            return $shipping->withPhone($stripeShipping->phone);
        }

        return $shipping;
    }

    /** @returns Collection<StripeSubscription> */
    public function subscriptions(bool $refresh = false): Collection
    {
        if ($this->subscriptions instanceof Collection && !$refresh) {
            return $this->subscriptions;
        }

        if (is_null($this->id)) {
            throw new ClassPropertyNullException("id");
        }

        $this->subscriptions = app(StripeSubscriptionService::class)->getAllForCustomer($this->id);

        return $this->subscriptions;
    }

    /** @returns Collection<StripePaymentMethod> */
    public function paymentMethods(bool $refresh = false): Collection
    {
        if ($this->paymentMethods instanceof Collection && !$refresh) {
            return $this->paymentMethods;
        }

        if (is_null($this->id)) {
            throw new ClassPropertyNullException("id");
        }

        $this->paymentMethods = app(StripePaymentMethodService::class)->getAllForCustomer($this->id);

        return $this->paymentMethods;
    }

    public function addPaymentMethod(StripePaymentMethod $paymentMethod): self
    {
        $paymentMethod = app(StripePaymentMethodService::class)->create($paymentMethod);
        $paymentMethodId = $paymentMethod->id();

        if (is_null($paymentMethodId)) {
            throw new VariableNullException("paymentMethodId");
        }

        if (is_null($this->id)) {
            throw new ClassPropertyNullException("id");
        }

        app(StripePaymentMethodService::class)->attach($paymentMethodId, $this->id);

        if (!is_null($this->paymentMethods)) {
            $this->paymentMethods(true);
        }

        return $this;
    }

    public function service(): StripeCustomerService
    {
        return app(StripeCustomerService::class);
    }

    public function toArray(): array
    {
        $array = [
            "id" => $this->id,
            "address" => $this->address?->toArray(),
            "description" => $this->description,
            "email" => $this->email,
            "name" => $this->name,
            "phone" => $this->phone,
            "shipping" => $this->shipping?->toArray(),
        ];

        return Arr::whereNotNull($array);
    }

    // Fluent setters
    public function withId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function withAddress(StripeAddress $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function withDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function withEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function withName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function withPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function withShipping(StripeShipping $shipping): self
    {
        $this->shipping = $shipping;

        return $this;
    }

    // Getter methods
    public function id(): ?string
    {
        return $this->id;
    }

    public function address(): ?StripeAddress
    {
        return $this->address;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function shipping(): ?StripeShipping
    {
        return $this->shipping;
    }
}