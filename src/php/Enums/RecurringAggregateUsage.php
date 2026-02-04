<?php

namespace EncoreDigitalGroup\Stripe\Enums;

enum RecurringAggregateUsage: string
{
    case Sum = "sum";
    case LastDuringPeriod = "last_during_period";
    case LastEver = "last_ever";
    case Max = "max";
}