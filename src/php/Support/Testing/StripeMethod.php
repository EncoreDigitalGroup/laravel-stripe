<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Support\Testing;

enum StripeMethod: string
{
    // Customer methods
    case CustomersCreate = "customers.create";
    case CustomersRetrieve = "customers.retrieve";
    case CustomersUpdate = "customers.update";
    case CustomersDelete = "customers.delete";
    case CustomersAll = "customers.all";
    case CustomersSearch = "customers.search";

    // Product methods
    case ProductsCreate = "products.create";
    case ProductsRetrieve = "products.retrieve";
    case ProductsUpdate = "products.update";
    case ProductsDelete = "products.delete";
    case ProductsAll = "products.all";
    case ProductsSearch = "products.search";

    // Price methods
    case PricesCreate = "prices.create";
    case PricesRetrieve = "prices.retrieve";
    case PricesUpdate = "prices.update";
    case PricesAll = "prices.all";
    case PricesSearch = "prices.search";

    // Subscription methods
    case SubscriptionsCreate = "subscriptions.create";
    case SubscriptionsRetrieve = "subscriptions.retrieve";
    case SubscriptionsUpdate = "subscriptions.update";
    case SubscriptionsDelete = "subscriptions.delete";
    case SubscriptionsAll = "subscriptions.all";
    case SubscriptionsSearch = "subscriptions.search";
    case SubscriptionsCancel = "subscriptions.cancel";

    // Subscription Schedule methods
    case SubscriptionSchedulesCreate = "subscriptionSchedules.create";
    case SubscriptionSchedulesRetrieve = "subscriptionSchedules.retrieve";
    case SubscriptionSchedulesUpdate = "subscriptionSchedules.update";
    case SubscriptionSchedulesCancel = "subscriptionSchedules.cancel";
    case SubscriptionSchedulesRelease = "subscriptionSchedules.release";
    case SubscriptionSchedulesAll = "subscriptionSchedules.all";

    // Payment Method methods
    case PaymentMethodsCreate = "paymentMethods.create";
    case PaymentMethodsRetrieve = "paymentMethods.retrieve";
    case PaymentMethodsUpdate = "paymentMethods.update";
    case PaymentMethodsAttach = "paymentMethods.attach";
    case PaymentMethodsDetach = "paymentMethods.detach";
    case PaymentMethodsAll = "paymentMethods.all";

    // Invoice methods
    case InvoicesCreate = "invoices.create";
    case InvoicesRetrieve = "invoices.retrieve";
    case InvoicesUpdate = "invoices.update";
    case InvoicesDelete = "invoices.delete";
    case InvoicesAll = "invoices.all";
    case InvoicesFinalize = "invoices.finalizeInvoice";
    case InvoicesPay = "invoices.pay";
    case InvoicesSend = "invoices.sendInvoice";

    // Charge methods
    case ChargesCreate = "charges.create";
    case ChargesRetrieve = "charges.retrieve";
    case ChargesUpdate = "charges.update";
    case ChargesAll = "charges.all";
    case ChargesCapture = "charges.capture";

    // Refund methods
    case RefundsCreate = "refunds.create";
    case RefundsRetrieve = "refunds.retrieve";
    case RefundsUpdate = "refunds.update";
    case RefundsAll = "refunds.all";

    // Wildcard patterns for matching any method on a service
    case CustomersAny = "customers.*";
    case ProductsAny = "products.*";
    case PricesAny = "prices.*";
    case SubscriptionsAny = "subscriptions.*";
    case SubscriptionSchedulesAny = "subscriptionSchedules.*";
    case PaymentMethodsAny = "paymentMethods.*";
    case InvoicesAny = "invoices.*";
    case ChargesAny = "charges.*";
    case RefundsAny = "refunds.*";
}
