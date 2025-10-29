<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Customer;

use EncoreDigitalGroup\StdLib\Exceptions\NullExceptions\ClassPropertyNullException;
use EncoreDigitalGroup\StdLib\Exceptions\NullExceptions\VariableNullException;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Objects\Payment\StripePaymentMethod;
use EncoreDigitalGroup\Stripe\Objects\Payment\StripeSetupIntent;
use EncoreDigitalGroup\Stripe\Objects\Subscription\StripeSubscription;
use EncoreDigitalGroup\Stripe\Objects\Support\StripeAddress;
use EncoreDigitalGroup\Stripe\Services\StripeCustomerService;
use EncoreDigitalGroup\Stripe\Services\StripePaymentMethodService;
use EncoreDigitalGroup\Stripe\Services\StripeSubscriptionService;
use EncoreDigitalGroup\Stripe\Support\Traits\HasGet;
use EncoreDigitalGroup\Stripe\Support\Traits\HasSave;
use Illuminate\Support\Collection;
use InvalidArgumentException;
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
    private ?bool $hasDefaultPaymentMethod = null;
    private ?string $defaultPaymentMethod = null;

    /**
     * Create a StripeCustomer instance from a Stripe API Customer object
     */
    public static function fromStripeObject(Customer $stripeCustomer): self
    {
        $instance = self::make();

        if ($stripeCustomer->id) {
            $instance = $instance->withId($stripeCustomer->id);
        }

        $instance = self::applyBasicProperties($instance, $stripeCustomer);
        $instance = self::applyShippingIfPresent($instance, $stripeCustomer);

        return self::applyDefaultPaymentMethod($instance, $stripeCustomer);
    }

    private static function applyBasicProperties(self $instance, Customer $stripeCustomer): self
    {
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

        return $instance;
    }

    private static function applyShippingIfPresent(self $instance, Customer $stripeCustomer): self
    {
        if (!isset($stripeCustomer->shipping)) {
            return $instance;
        }

        /** @var StripeObject $stripeShipping */
        $stripeShipping = $stripeCustomer->shipping;
        $shipping = self::extractShipping($stripeShipping);

        if ($shipping instanceof StripeShipping) {
            return $instance->withShipping($shipping);
        }

        return $instance;
    }

    private static function applyDefaultPaymentMethod(self $instance, Customer $stripeCustomer): self
    {
        if (!isset($stripeCustomer->invoice_settings)) {
            return $instance;
        }

        $defaultPaymentMethod = $stripeCustomer->invoice_settings->default_payment_method ?? null;

        if (is_string($defaultPaymentMethod)) {
            $instance->defaultPaymentMethod = $defaultPaymentMethod;
            $instance->hasDefaultPaymentMethod = true;
        }

        if (is_object($defaultPaymentMethod)) {
            $instance->defaultPaymentMethod = $defaultPaymentMethod->id;
            $instance->hasDefaultPaymentMethod = true;
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

    public function createSetupIntent(): StripeSetupIntent
    {
        if (is_null($this->id)) {
            throw new ClassPropertyNullException("id");
        }

        return StripeSetupIntent::make()->withCustomer($this->id);
    }

    public function hasDefaultPaymentMethod(): bool
    {
        if (is_null($this->id)) {
            throw new ClassPropertyNullException("id");
        }

        if (!is_null($this->hasDefaultPaymentMethod)) {
            return $this->hasDefaultPaymentMethod;
        }

        $this->hasDefaultPaymentMethod = $this->service()->hasDefaultPaymentMethod($this->id);

        return $this->hasDefaultPaymentMethod;
    }

    public function save(): self
    {
        if (!is_null($this->defaultPaymentMethod)) {
            if (is_null($this->id)) {
                throw new ClassPropertyNullException("id");
            }

            $paymentMethods = $this->paymentMethods();
            $paymentMethodExists = $paymentMethods->contains(fn($pm) => $pm->id() === $this->defaultPaymentMethod);

            if (!$paymentMethodExists) {
                throw new InvalidArgumentException("Payment method {$this->defaultPaymentMethod} is not attached to customer {$this->id}");
            }
        }

        return is_null($this->id) ? $this->service()->create($this) : $this->service()->update($this->id, $this);
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

        if (!is_null($this->defaultPaymentMethod)) {
            $array["invoice_settings"] = [
                "default_payment_method" => $this->defaultPaymentMethod,
            ];
        }

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

    public function withDefaultPaymentMethod(string $defaultPaymentMethod): self
    {
        $this->defaultPaymentMethod = $defaultPaymentMethod;

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