<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Services;

use EncoreDigitalGroup\Stripe\Enums\PaymentMethodType;
use EncoreDigitalGroup\Stripe\Objects\Payment\StripePaymentMethod;
use EncoreDigitalGroup\Stripe\Support\Traits\HasStripe;
use Illuminate\Support\Collection;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentMethod;

/** @internal */
class StripePaymentMethodService
{
    use HasStripe;

    /** @throws ApiErrorException */
    public function create(StripePaymentMethod $paymentMethod): StripePaymentMethod
    {
        $data = $paymentMethod->toArray();

        unset($data["id"], $data["created"]);

        $stripePaymentMethod = $this->stripe->paymentMethods->create($data);

        return StripePaymentMethod::fromStripeObject($stripePaymentMethod);
    }

    /** @throws ApiErrorException */
    public function get(string $paymentMethodId): StripePaymentMethod
    {
        $stripePaymentMethod = $this->stripe->paymentMethods->retrieve($paymentMethodId);

        return StripePaymentMethod::fromStripeObject($stripePaymentMethod);
    }

    /**
     * @return Collection<int, StripePaymentMethod>
     *
     * @throws ApiErrorException
     */
    public function getAllForCustomer(string $customerId, PaymentMethodType $paymentMethodType = PaymentMethodType::Card): Collection
    {
        return $this->list([
            "customer" => $customerId,
            "type" => $paymentMethodType->value,
        ]);
    }

    /** @throws ApiErrorException */
    public function update(string $paymentMethodId, StripePaymentMethod $paymentMethod): StripePaymentMethod
    {
        $data = $paymentMethod->toArray();

        unset($data["id"], $data["created"], $data["type"]);

        $stripePaymentMethod = $this->stripe->paymentMethods->update($paymentMethodId, $data);

        return StripePaymentMethod::fromStripeObject($stripePaymentMethod);
    }

    /** @throws ApiErrorException */
    public function attach(string $paymentMethodId, string $customerId): StripePaymentMethod
    {
        $stripePaymentMethod = $this->stripe->paymentMethods->attach($paymentMethodId, [
            "customer" => $customerId,
        ]);

        return StripePaymentMethod::fromStripeObject($stripePaymentMethod);
    }

    /** @throws ApiErrorException */
    public function detach(string $paymentMethodId): StripePaymentMethod
    {
        $stripePaymentMethod = $this->stripe->paymentMethods->detach($paymentMethodId);

        return StripePaymentMethod::fromStripeObject($stripePaymentMethod);
    }

    /**
     * @return Collection<int, StripePaymentMethod>
     *
     * @throws ApiErrorException
     */
    public function list(array $params = []): Collection
    {
        $stripePaymentMethods = $this->stripe->paymentMethods->all($params);

        return collect($stripePaymentMethods->data)
            ->map(fn (PaymentMethod $stripePaymentMethod): StripePaymentMethod => StripePaymentMethod::fromStripeObject($stripePaymentMethod));
    }
}
