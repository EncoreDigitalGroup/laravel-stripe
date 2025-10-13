<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe\Objects\Subscription;

use EncoreDigitalGroup\Common\Stripe\Enums\CollectionMethod;
use EncoreDigitalGroup\Common\Stripe\Enums\SubscriptionStatus;
use EncoreDigitalGroup\Common\Stripe\Support\HasMake;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use Stripe\Subscription;

class StripeSubscription
{
    use HasMake;

    public function __construct(
        public ?string $id = null,
        public ?string $customer = null,
        public ?SubscriptionStatus $status = null,
        public ?int $currentPeriodStart = null,
        public ?int $currentPeriodEnd = null,
        public ?int $cancelAt = null,
        public ?int $canceledAt = null,
        public ?int $trialStart = null,
        public ?int $trialEnd = null,
        public ?array $items = null,
        public ?string $defaultPaymentMethod = null,
        public ?array $metadata = null,
        public ?string $currency = null,
        public ?CollectionMethod $collectionMethod = null,
        public ?int $billingCycleAnchor = null,
        public ?bool $cancelAtPeriodEnd = null,
        public ?int $daysUntilDue = null,
        public ?string $description = null
    ) {}

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

        return self::make(
            id: $stripeSubscription->id,
            customer: $customer,
            status: $status,
            currentPeriodStart: $stripeSubscription->current_period_start ?? null,
            currentPeriodEnd: $stripeSubscription->current_period_end ?? null,
            cancelAt: $stripeSubscription->cancel_at ?? null,
            canceledAt: $stripeSubscription->canceled_at ?? null,
            trialStart: $stripeSubscription->trial_start ?? null,
            trialEnd: $stripeSubscription->trial_end ?? null,
            items: $items,
            defaultPaymentMethod: $defaultPaymentMethod,
            metadata: $stripeSubscription->metadata->toArray(),
            currency: $stripeSubscription->currency ?? null,
            collectionMethod: $collectionMethod,
            billingCycleAnchor: $stripeSubscription->billing_cycle_anchor ?? null,
            cancelAtPeriodEnd: $stripeSubscription->cancel_at_period_end ?? null,
            daysUntilDue: $stripeSubscription->days_until_due ?? null,
            description: $stripeSubscription->description ?? null
        );
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

    public function toArray(): array
    {
        $array = [
            "id" => $this->id,
            "customer" => $this->customer,
            "status" => $this->status?->value,
            "current_period_start" => $this->currentPeriodStart,
            "current_period_end" => $this->currentPeriodEnd,
            "cancel_at" => $this->cancelAt,
            "canceled_at" => $this->canceledAt,
            "trial_start" => $this->trialStart,
            "trial_end" => $this->trialEnd,
            "items" => $this->items,
            "default_payment_method" => $this->defaultPaymentMethod,
            "metadata" => $this->metadata,
            "currency" => $this->currency,
            "collection_method" => $this->collectionMethod?->value,
            "billing_cycle_anchor" => $this->billingCycleAnchor,
            "cancel_at_period_end" => $this->cancelAtPeriodEnd,
            "days_until_due" => $this->daysUntilDue,
            "description" => $this->description,
        ];

        return Arr::whereNotNull($array);
    }
}