<?php

namespace EncoreDigitalGroup\Stripe\Enums;

enum ProrationBehavior: string
{
    case CreateProrations = "create_prorations";
    case None = "none";
    case AlwaysInvoice = "always_invoice";
}