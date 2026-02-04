<?php

namespace EncoreDigitalGroup\Stripe\Enums;

enum InvoiceStatus: string
{
    case Draft = "draft";
    case Open = "open";
    case Paid = "paid";
    case Uncollectible = "uncollectible";
    case Void = "void";
}
