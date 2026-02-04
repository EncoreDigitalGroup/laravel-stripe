<?php

namespace EncoreDigitalGroup\Stripe\Enums;

enum InvoiceBillingReason: string
{
    case AutomaticPendingInvoiceItemInvoice = "automatic_pending_invoice_item_invoice";
    case Manual = "manual";
    case QuoteAccept = "quote_accept";
    case Subscription = "subscription";
    case SubscriptionCreate = "subscription_create";
    case SubscriptionCycle = "subscription_cycle";
    case SubscriptionThreshold = "subscription_threshold";
    case SubscriptionUpdate = "subscription_update";
    case Upcoming = "upcoming";
}
