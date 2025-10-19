<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Enums;

enum PaymentIntentConfirmationMethod: string
{
    case Automatic = "automatic";
    case Manual = "manual";
}
