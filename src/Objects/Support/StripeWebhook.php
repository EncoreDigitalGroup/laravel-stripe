<?php
/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe\Objects\Support;

use EncoreDigitalGroup\Common\Stripe\Support\HasMake;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Str;
use Illuminate\Support\Facades\Request;
use Stripe\Event as StripeEvent;
use Stripe\Webhook;

class StripeWebhook
{
    use HasMake;

    public function getWebhookSignatureHeader(): string
    {
        return Request::header("HTTP_STRIPE_SIGNATURE", Str::empty());
    }

    public function fromRequest(string $payload, string $signature, string $secret): StripeEvent
    {
        return Webhook::constructEvent($payload, $signature, $secret);
    }
}