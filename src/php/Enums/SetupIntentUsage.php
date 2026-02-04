<?php

namespace EncoreDigitalGroup\Stripe\Enums;

enum SetupIntentUsage: string
{
    case OnSession = "on_session";
    case OffSession = "off_session";
}