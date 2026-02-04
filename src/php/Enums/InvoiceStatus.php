<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Enums;

enum InvoiceStatus: string
{
    case Draft = "draft";
    case Open = "open";
    case Paid = "paid";
    case Uncollectible = "uncollectible";
    case Void = "void";
}
