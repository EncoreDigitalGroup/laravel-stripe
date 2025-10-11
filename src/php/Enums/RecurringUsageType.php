<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe\Enums;

enum RecurringUsageType: string
{
    case Metered = "metered";
    case Licensed = "licensed";
}