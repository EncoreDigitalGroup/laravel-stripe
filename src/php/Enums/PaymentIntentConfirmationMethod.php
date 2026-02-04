<?php

namespace EncoreDigitalGroup\Stripe\Enums;

enum PaymentIntentConfirmationMethod: string
{
    case Automatic = "automatic";
    case Manual = "manual";
}
