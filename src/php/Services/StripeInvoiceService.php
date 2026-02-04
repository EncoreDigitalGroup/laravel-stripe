<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Services;

use EncoreDigitalGroup\Stripe\Objects\Invoice\StripeInvoice;
use EncoreDigitalGroup\Stripe\Support\Traits\HasStripe;
use Illuminate\Support\Collection;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice;

/** @internal */
class StripeInvoiceService
{
    use HasStripe;

    /** @throws ApiErrorException */
    public function get(string $invoiceId): StripeInvoice
    {
        $stripeInvoice = $this->stripe->invoices->retrieve($invoiceId);

        return StripeInvoice::fromStripeObject($stripeInvoice);
    }

    /**
     * @return Collection<int, StripeInvoice>
     *
     * @throws ApiErrorException
     */
    public function list(array $params = []): Collection
    {
        $stripeInvoices = $this->stripe->invoices->all($params);

        return collect($stripeInvoices->data)
            ->map(fn (Invoice $stripeInvoice): StripeInvoice => StripeInvoice::fromStripeObject($stripeInvoice));
    }
}
