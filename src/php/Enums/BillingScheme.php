<?php

namespace EncoreDigitalGroup\Stripe\Enums;

enum BillingScheme: string
{
    case PerUnit = "per_unit";
    case Tiered = "tiered";
}