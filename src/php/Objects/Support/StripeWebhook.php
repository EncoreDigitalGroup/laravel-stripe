<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Support;

use EncoreDigitalGroup\StdLib\Objects\Support\Types\Str;
use EncoreDigitalGroup\Stripe\Support\HasMake;
use Illuminate\Support\Facades\Request;
use Stripe\Event as StripeEvent;
use Stripe\Webhook;

class StripeWebhook
{
    use HasMake;

    public function __construct(
        public string $url,
        public array $events = []
    ) {}

    public static function getWebhookSignatureHeader(): string
    {
        return Request::header("stripe-signature", Str::empty());
    }

    public static function fromRequest(string $payload, string $signature, string $secret): StripeEvent
    {
        return Webhook::constructEvent($payload, $signature, $secret);
    }

    public function toArray(): array
    {
        return [
            "enabled_events" => $this->events,
            "url" => $this->url,
        ];
    }
}