<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Support;

use EncoreDigitalGroup\StdLib\Objects\Support\Types\Str;
use Illuminate\Support\Facades\Request;
use Stripe\Event as StripeEvent;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookHelper
{
    public static function getSignatureHeader(): string
    {
        return Request::header("stripe-signature", Str::empty());
    }

    /** @throws SignatureVerificationException */
    public static function constructEvent(string $payload, string $signature, string $secret): StripeEvent
    {
        return Webhook::constructEvent($payload, $signature, $secret);
    }
}
