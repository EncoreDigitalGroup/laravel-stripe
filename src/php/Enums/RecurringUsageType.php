<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Enums;

enum RecurringUsageType: string
{
    case Metered = "metered";
    case Licensed = "licensed";
}