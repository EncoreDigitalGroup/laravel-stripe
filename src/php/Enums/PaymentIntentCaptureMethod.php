<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Enums;

enum PaymentIntentCaptureMethod: string
{
    case Automatic = "automatic";
    case AutomaticAsync = "automatic_async";
    case Manual = "manual";
}
