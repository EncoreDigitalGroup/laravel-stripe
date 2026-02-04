<?php

namespace EncoreDigitalGroup\Stripe\Enums;

enum RecurringInterval: string
{
    case Day = "day";
    case Week = "week";
    case Month = "month";
    case Year = "year";
}