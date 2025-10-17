<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Enums;

enum SubscriptionScheduleEndBehavior: string
{
    case Release = "release";
    case Cancel = "cancel";
}