<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Enums;

enum SetupIntentUsage: string
{
    case OnSession = "on_session";
    case OffSession = "off_session";
}