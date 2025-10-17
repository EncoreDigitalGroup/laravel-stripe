<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Webhook;

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Support\HasMake;
use EncoreDigitalGroup\Stripe\Support\HasTimestamps;

class StripeWebhookEvent
{
    use HasMake;
    use HasTimestamps;

    public function __construct(
        public ?string                                                            $id = null,
        public ?string                                                            $type = null,
        public StripeInvoiceWebhookData|StripePaymentIntentWebhookData|array|null $data = null,
        public ?CarbonImmutable                                                   $created = null,
        public ?bool                                                              $livemode = null,
        public ?string                                                            $apiVersion = null
    ) {}

    /**
     * Create a StripeWebhookEvent instance from a Stripe webhook event array
     */
    public static function fromWebhookData(array $event): self
    {
        $data = null;
        $eventData = $event["data"]["object"] ?? null;

        if (is_array($eventData)) {
            $eventType = $event["type"] ?? "";

            if (str_starts_with($eventType, "invoice.")) {
                $data = StripeInvoiceWebhookData::fromWebhookData($eventData);
            } elseif (str_starts_with($eventType, "payment_intent.")) {
                $data = StripePaymentIntentWebhookData::fromWebhookData($eventData);
            } else {
                $data = $eventData;
            }
        }

        return self::make(
            id: $event["id"] ?? null,
            type: $event["type"] ?? null,
            data: $data,
            created: self::timestampToCarbon($event["created"] ?? null),
            livemode: $event["livemode"] ?? null,
            apiVersion: $event["api_version"] ?? null
        );
    }

    /**
     * Get the data as StripeInvoiceWebhookData if the event is invoice-related
     */
    public function asInvoiceData(): ?StripeInvoiceWebhookData
    {
        return $this->data instanceof StripeInvoiceWebhookData ? $this->data : null;
    }

    /**
     * Get the data as StripePaymentIntentWebhookData if the event is payment intent-related
     */
    public function asPaymentIntentData(): ?StripePaymentIntentWebhookData
    {
        return $this->data instanceof StripePaymentIntentWebhookData ? $this->data : null;
    }

    /**
     * Get the raw data array if the event type is not specifically handled
     */
    public function asRawData(): ?array
    {
        return is_array($this->data) ? $this->data : null;
    }

    public function toArray(): array
    {
        $dataArray = null;
        if ($this->data instanceof StripeInvoiceWebhookData || $this->data instanceof StripePaymentIntentWebhookData) {
            $dataArray = $this->data->toArray();
        } elseif (is_array($this->data)) {
            $dataArray = $this->data;
        }

        $array = [
            "id" => $this->id,
            "type" => $this->type,
            "data" => $dataArray ? ["object" => $dataArray] : null,
            "created" => self::carbonToTimestamp($this->created),
            "livemode" => $this->livemode,
            "api_version" => $this->apiVersion,
        ];

        return Arr::whereNotNull($array);
    }
}