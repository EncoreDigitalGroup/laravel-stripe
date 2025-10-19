<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Webhook\Payloads;

interface IWebhookData
{
    public static function fromStripeObject(object $stripeObject): self;
}