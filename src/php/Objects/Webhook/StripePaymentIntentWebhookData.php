<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Webhook;

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Support\HasTimestamps;
use PHPGenesis\Common\Traits\HasMake;

class StripePaymentIntentWebhookData
{
    use HasMake;
    use HasTimestamps;

    public function __construct(
        public ?string          $id = null,
        public ?string          $status = null,
        public ?int             $amount = null,
        public ?int             $amountReceived = null,
        public ?string          $currency = null,
        public ?string          $customer = null,
        public ?string          $invoice = null,
        public ?string          $paymentMethod = null,
        public ?string          $description = null,
        public ?string          $cancellationReason = null,
        public ?array           $lastPaymentError = null,
        public ?CarbonImmutable $created = null,
        public ?array           $metadata = null
    ) {}

    /**
     * Create a StripePaymentIntentWebhookData instance from a Stripe webhook payment intent array
     */
    public static function fromWebhookData(array $paymentIntent): self
    {
        return self::make(
            id: $paymentIntent["id"] ?? null,
            status: $paymentIntent["status"] ?? null,
            amount: $paymentIntent["amount"] ?? null,
            amountReceived: $paymentIntent["amount_received"] ?? null,
            currency: $paymentIntent["currency"] ?? null,
            customer: $paymentIntent["customer"] ?? null,
            invoice: $paymentIntent["invoice"] ?? null,
            paymentMethod: $paymentIntent["payment_method"] ?? null,
            description: $paymentIntent["description"] ?? null,
            cancellationReason: $paymentIntent["cancellation_reason"] ?? null,
            lastPaymentError: $paymentIntent["last_payment_error"] ?? null,
            created: self::timestampToCarbon($paymentIntent["created"] ?? null),
            metadata: $paymentIntent["metadata"] ?? null
        );
    }

    public function toArray(): array
    {
        $array = [
            "id" => $this->id,
            "status" => $this->status,
            "amount" => $this->amount,
            "amount_received" => $this->amountReceived,
            "currency" => $this->currency,
            "customer" => $this->customer,
            "invoice" => $this->invoice,
            "payment_method" => $this->paymentMethod,
            "description" => $this->description,
            "cancellation_reason" => $this->cancellationReason,
            "last_payment_error" => $this->lastPaymentError,
            "created" => self::carbonToTimestamp($this->created),
            "metadata" => $this->metadata,
        ];

        return Arr::whereNotNull($array);
    }
}