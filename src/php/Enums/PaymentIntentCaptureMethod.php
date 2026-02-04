<?php

namespace EncoreDigitalGroup\Stripe\Enums;

enum PaymentIntentCaptureMethod: string
{
    case Automatic = "automatic";
    case AutomaticAsync = "automatic_async";
    case Manual = "manual";
}
