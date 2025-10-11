<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe\Enums;

enum RecurringAggregateUsage: string
{
    case Sum = "sum";
    case LastDuringPeriod = "last_during_period";
    case LastEver = "last_ever";
    case Max = "max";
}