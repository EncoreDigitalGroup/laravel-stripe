<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Enums;

enum SubscriptionStatus: string
{
    case Incomplete = "incomplete";
    case IncompleteExpired = "incomplete_expired";
    case Trialing = "trialing";
    case Active = "active";
    case PastDue = "past_due";
    case Canceled = "canceled";
    case Unpaid = "unpaid";
    case Paused = "paused";
}