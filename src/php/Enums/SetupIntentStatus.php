<?php

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
