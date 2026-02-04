<?php

namespace EncoreDigitalGroup\Stripe\Enums;

enum SubscriptionScheduleEndBehavior: string
{
    case Release = "release";
    case Cancel = "cancel";
}