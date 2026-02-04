<?php

namespace EncoreDigitalGroup\Stripe\Enums;

enum SubscriptionScheduleProrationBehavior: string
{
    case CreateProrations = "create_prorations";
    case None = "none";
    case AlwaysInvoice = "always_invoice";
}