<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Support\Building\Builders;

use EncoreDigitalGroup\Stripe\Objects\Support\StripeWebhook;

class WebhookBuilder
{
    public function build(mixed ...$params): StripeWebhook
    {
        return StripeWebhook::make(...$params);
    }
}