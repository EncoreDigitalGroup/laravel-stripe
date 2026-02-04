<?php

namespace EncoreDigitalGroup\Stripe\Objects\Webhook\Payloads;

interface IWebhookData
{
    public static function fromStripeObject(object $stripeObject): self;
}