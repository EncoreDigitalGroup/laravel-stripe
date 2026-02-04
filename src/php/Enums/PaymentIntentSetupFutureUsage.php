<?php

namespace EncoreDigitalGroup\Stripe\Enums;

enum PaymentIntentSetupFutureUsage: string
{
    case OnSession = "on_session";
    case OffSession = "off_session";
}
