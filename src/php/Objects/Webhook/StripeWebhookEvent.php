<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Webhook;

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Objects\Webhook\Payloads\IWebhookData;
use EncoreDigitalGroup\Stripe\Objects\Webhook\Payloads\StripeInvoiceWebhookData;
use EncoreDigitalGroup\Stripe\Objects\Webhook\Payloads\StripePaymentIntentWebhookData;
use EncoreDigitalGroup\Stripe\Support\StripeWebhookHelper;
use EncoreDigitalGroup\Stripe\Support\Traits\HasIdentifier;
use EncoreDigitalGroup\Stripe\Support\Traits\HasLivemode;
use EncoreDigitalGroup\Stripe\Support\Traits\HasTimestamps;
use PHPGenesis\Common\Traits\HasMake;
use Stripe\Event as StripeEvent;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookEvent
{
    use HasIdentifier;
    use HasLivemode;
    use HasMake;
    use HasTimestamps;

    private ?string $type = null;
    private IWebhookData|array|null $data = null;
    private ?CarbonImmutable $created = null;
    private ?string $apiVersion = null;

    /** Create a StripeWebhookEvent from a verified Stripe Event object */
    public static function fromStripeEvent(StripeEvent $event): self
    {
        $data = null;
        $eventData = $event->data->object ?? null;

        if ($eventData !== null) {
            $eventType = $event->type ?? "";

            if (str_starts_with($eventType, "invoice.")) {
                $data = StripeInvoiceWebhookData::fromStripeObject($eventData);
            } elseif (str_starts_with($eventType, "payment_intent.")) {
                $data = StripePaymentIntentWebhookData::fromStripeObject($eventData);
            } else {
                $dataJson = json_encode($eventData);
                $data = $dataJson !== false ? json_decode($dataJson, true) : [];
            }
        }

        return self::make()
            ->withId($event->id)
            ->withType($event->type)
            ->withData($data)
            ->withCreated(self::timestampToCarbon($event->created))
            ->withLivemode($event->livemode ?? false)
            ->withApiVersion($event->api_version ?? null);
    }

    /**
     * Create a StripeWebhookEvent from raw webhook request data
     *
     * @throws SignatureVerificationException
     */
    public static function fromRequest(string $payload, string $signature, string $secret): self
    {
        $event = StripeWebhookHelper::constructEvent($payload, $signature, $secret);

        return self::fromStripeEvent($event);
    }

    public function withType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function type(): ?string
    {
        return $this->type;
    }

    public function withData(IWebhookData|array|null $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function data(): IWebhookData|array|null
    {
        return $this->data;
    }

    public function withCreated(?CarbonImmutable $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function created(): ?CarbonImmutable
    {
        return $this->created;
    }

    public function withApiVersion(?string $apiVersion): self
    {
        $this->apiVersion = $apiVersion;

        return $this;
    }

    public function apiVersion(): ?string
    {
        return $this->apiVersion;
    }

    /** Get the data as StripeInvoiceWebhookData if the event is invoice-related */
    public function asInvoiceData(): ?StripeInvoiceWebhookData
    {
        return $this->data instanceof StripeInvoiceWebhookData ? $this->data : null;
    }

    /** Get the data as StripePaymentIntentWebhookData if the event is payment intent-related */
    public function asPaymentIntentData(): ?StripePaymentIntentWebhookData
    {
        return $this->data instanceof StripePaymentIntentWebhookData ? $this->data : null;
    }

    /** Get the raw data array if the event type is not specifically handled */
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
            "data" => $dataArray !== null && $dataArray !== [] ? ["object" => $dataArray] : null,
            "created" => self::carbonToTimestamp($this->created),
            "livemode" => $this->livemode,
            "api_version" => $this->apiVersion,
        ];

        return Arr::whereNotNull($array);
    }
}