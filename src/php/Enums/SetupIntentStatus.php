<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Enums;

enum SetupIntentStatus: string
{
    case RequiresPaymentMethod = "requires_payment_method";
    case RequiresConfirmation = "requires_confirmation";
    case RequiresAction = "requires_action";
    case Processing = "processing";
    case Canceled = "canceled";
    case Succeeded = "succeeded";
}
