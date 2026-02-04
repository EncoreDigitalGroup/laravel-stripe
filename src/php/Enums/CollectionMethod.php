<?php

namespace EncoreDigitalGroup\Stripe\Enums;

enum CollectionMethod: string
{
    case ChargeAutomatically = "charge_automatically";
    case SendInvoice = "send_invoice";
}