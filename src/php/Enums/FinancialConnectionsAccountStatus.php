<?php

namespace EncoreDigitalGroup\Stripe\Enums;

enum FinancialConnectionsAccountStatus: string
{
    case Active = "active";
    case Inactive = "inactive";
    case Disconnected = "disconnected";
}
