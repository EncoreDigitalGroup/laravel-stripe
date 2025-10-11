<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe\Enums;

enum BillingScheme: string
{
    case PerUnit = "per_unit";
    case Tiered = "tiered";
}