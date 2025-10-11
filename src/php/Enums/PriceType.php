<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe\Enums;

enum PriceType: string
{
    case OneTime = 'one_time';
    case Recurring = 'recurring';
}