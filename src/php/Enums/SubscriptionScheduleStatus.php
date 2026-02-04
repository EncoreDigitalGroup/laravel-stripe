<?php

namespace EncoreDigitalGroup\Stripe\Enums;

enum SubscriptionScheduleStatus: string
{
    case NotStarted = "not_started";
    case Active = "active";
    case Completed = "completed";
    case Released = "released";
    case Canceled = "canceled";
}