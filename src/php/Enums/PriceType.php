<?php

namespace EncoreDigitalGroup\Stripe\Enums;

enum PriceType: string
{
    case OneTime = "one_time";
    case Recurring = "recurring";
}