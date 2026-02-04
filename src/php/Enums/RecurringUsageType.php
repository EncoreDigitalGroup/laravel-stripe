<?php

namespace EncoreDigitalGroup\Stripe\Enums;

enum RecurringUsageType: string
{
    case Metered = "metered";
    case Licensed = "licensed";
}