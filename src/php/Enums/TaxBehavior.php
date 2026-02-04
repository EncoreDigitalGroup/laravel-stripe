<?php

namespace EncoreDigitalGroup\Stripe\Enums;

enum TaxBehavior: string
{
    case Inclusive = "inclusive";
    case Exclusive = "exclusive";
    case Unspecified = "unspecified";
}