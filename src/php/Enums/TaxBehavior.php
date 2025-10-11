<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe\Enums;

enum TaxBehavior: string
{
    case Inclusive = "inclusive";
    case Exclusive = "exclusive";
    case Unspecified = "unspecified";
}