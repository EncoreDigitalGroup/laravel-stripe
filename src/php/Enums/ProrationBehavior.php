<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Enums;

enum ProrationBehavior: string
{
    case CreateProrations = "create_prorations";
    case None = "none";
    case AlwaysInvoice = "always_invoice";
}