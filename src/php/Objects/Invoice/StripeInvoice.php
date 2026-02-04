<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Invoice;

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Enums\CollectionMethod;
use EncoreDigitalGroup\Stripe\Enums\InvoiceBillingReason;
use EncoreDigitalGroup\Stripe\Enums\InvoiceStatus;
use EncoreDigitalGroup\Stripe\Support\Traits\HasMetadata;
use EncoreDigitalGroup\Stripe\Support\Traits\HasReadOnlyFields;
use EncoreDigitalGroup\Stripe\Support\Traits\HasTimestamps;
use Illuminate\Support\Collection;
use PHPGenesis\Common\Traits\HasMake;
use Stripe\Invoice;
use Stripe\InvoiceLineItem;

class StripeInvoice
{
    use HasMake;
    use HasMetadata;
    use HasReadOnlyFields;
    use HasTimestamps;

    private ?string $id = null;
    private ?string $number = null;
    private ?string $customer = null;
    private ?string $subscription = null;
    private ?InvoiceStatus $status = null;
    private ?InvoiceBillingReason $billingReason = null;
    private ?CollectionMethod $collectionMethod = null;
    private ?string $currency = null;
    private ?int $amountDue = null;
    private ?int $amountPaid = null;
    private ?int $amountRemaining = null;
    private ?int $subtotal = null;
    private ?int $total = null;
    private ?int $tax = null;
    private ?bool $paid = null;
    private ?bool $attempted = null;
    private ?int $attemptCount = null;
    private ?string $hostedInvoiceUrl = null;
    private ?string $invoicePdf = null;
    private ?string $paymentIntent = null;
    private ?CarbonImmutable $created = null;
    private ?CarbonImmutable $dueDate = null;
    private ?CarbonImmutable $periodStart = null;
    private ?CarbonImmutable $periodEnd = null;

    /** @var ?Collection<int, StripeInvoiceLineItem> */
    private ?Collection $lines = null;

    public static function fromStripeObject(Invoice $stripeInvoice): self
    {
        $instance = self::make();

        $instance = self::setBasicFields($instance, $stripeInvoice);
        $instance = self::setRelatedIds($instance, $stripeInvoice);
        $instance = self::setStatusFields($instance, $stripeInvoice);
        $instance = self::setAmountFields($instance, $stripeInvoice);
        $instance = self::setTimestampFields($instance, $stripeInvoice);
        $instance = self::setLines($instance, $stripeInvoice);

        if (isset($stripeInvoice->metadata)) {
            $instance = $instance->withMetadata($stripeInvoice->metadata->toArray());
        }

        return $instance;
    }

    private static function setBasicFields(self $instance, Invoice $stripeInvoice): self
    {
        if ($stripeInvoice->id) {
            $instance = $instance->withId($stripeInvoice->id);
        }

        if ($stripeInvoice->number ?? null) {
            $instance = $instance->withNumber($stripeInvoice->number);
        }

        if ($stripeInvoice->currency ?? null) {
            $instance = $instance->withCurrency($stripeInvoice->currency);
        }

        if ($stripeInvoice->hosted_invoice_url ?? null) {
            $instance = $instance->withHostedInvoiceUrl($stripeInvoice->hosted_invoice_url);
        }

        if ($stripeInvoice->invoice_pdf ?? null) {
            $instance = $instance->withInvoicePdf($stripeInvoice->invoice_pdf);
        }

        return $instance;
    }

    private static function setRelatedIds(self $instance, Invoice $stripeInvoice): self
    {
        $customer = self::extractId($stripeInvoice->customer ?? null);
        if ($customer !== null) {
            $instance = $instance->withCustomer($customer);
        }

        $subscription = self::extractId($stripeInvoice->subscription ?? null);
        if ($subscription !== null) {
            $instance = $instance->withSubscription($subscription);
        }

        $paymentIntent = self::extractId($stripeInvoice->payment_intent ?? null);
        if ($paymentIntent !== null) {
            $instance = $instance->withPaymentIntent($paymentIntent);
        }

        return $instance;
    }

    private static function extractId(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        return $value->id ?? null;
    }

    private static function setStatusFields(self $instance, Invoice $stripeInvoice): self
    {
        if ($stripeInvoice->status ?? null) {
            $instance = $instance->withStatus(InvoiceStatus::from($stripeInvoice->status));
        }

        if ($stripeInvoice->billing_reason ?? null) {
            $instance = $instance->withBillingReason(InvoiceBillingReason::from($stripeInvoice->billing_reason));
        }

        if ($stripeInvoice->collection_method ?? null) {
            $instance = $instance->withCollectionMethod(CollectionMethod::from($stripeInvoice->collection_method));
        }

        if (isset($stripeInvoice->paid)) {
            $instance = $instance->withPaid($stripeInvoice->paid);
        }

        if (isset($stripeInvoice->attempted)) {
            $instance = $instance->withAttempted($stripeInvoice->attempted);
        }

        if ($stripeInvoice->attempt_count ?? null) {
            $instance = $instance->withAttemptCount($stripeInvoice->attempt_count);
        }

        return $instance;
    }

    private static function setAmountFields(self $instance, Invoice $stripeInvoice): self
    {
        if ($stripeInvoice->amount_due ?? null) {
            $instance = $instance->withAmountDue($stripeInvoice->amount_due);
        }

        if ($stripeInvoice->amount_paid ?? null) {
            $instance = $instance->withAmountPaid($stripeInvoice->amount_paid);
        }

        if (isset($stripeInvoice->amount_remaining)) {
            $instance = $instance->withAmountRemaining($stripeInvoice->amount_remaining);
        }

        if ($stripeInvoice->subtotal ?? null) {
            $instance = $instance->withSubtotal($stripeInvoice->subtotal);
        }

        if ($stripeInvoice->total ?? null) {
            $instance = $instance->withTotal($stripeInvoice->total);
        }

        if ($stripeInvoice->tax ?? null) {
            $instance = $instance->withTax($stripeInvoice->tax);
        }

        return $instance;
    }

    private static function setTimestampFields(self $instance, Invoice $stripeInvoice): self
    {
        $created = self::timestampToCarbon($stripeInvoice->created ?? null);
        if ($created instanceof CarbonImmutable) {
            $instance = $instance->withCreated($created);
        }

        $dueDate = self::timestampToCarbon($stripeInvoice->due_date ?? null);
        if ($dueDate instanceof CarbonImmutable) {
            $instance = $instance->withDueDate($dueDate);
        }

        $periodStart = self::timestampToCarbon($stripeInvoice->period_start ?? null);
        if ($periodStart instanceof CarbonImmutable) {
            $instance = $instance->withPeriodStart($periodStart);
        }

        $periodEnd = self::timestampToCarbon($stripeInvoice->period_end ?? null);
        if ($periodEnd instanceof CarbonImmutable) {
            $instance = $instance->withPeriodEnd($periodEnd);
        }

        return $instance;
    }

    private static function setLines(self $instance, Invoice $stripeInvoice): self
    {
        if (!isset($stripeInvoice->lines->data) || empty($stripeInvoice->lines->data)) {
            return $instance;
        }

        $lines = collect($stripeInvoice->lines->data)
            ->map(fn (InvoiceLineItem $lineItem): StripeInvoiceLineItem => StripeInvoiceLineItem::fromStripeObject($lineItem));

        return $instance->withLines($lines);
    }

    public function toArray(): array
    {
        $lines = null;
        if ($this->lines instanceof Collection) {
            $lines = $this->lines->map(fn (StripeInvoiceLineItem $item): array => $item->toArray())->all();
        }

        $array = [
            "id" => $this->id,
            "number" => $this->number,
            "customer" => $this->customer,
            "subscription" => $this->subscription,
            "status" => $this->status?->value,
            "billing_reason" => $this->billingReason?->value,
            "collection_method" => $this->collectionMethod?->value,
            "currency" => $this->currency,
            "amount_due" => $this->amountDue,
            "amount_paid" => $this->amountPaid,
            "amount_remaining" => $this->amountRemaining,
            "subtotal" => $this->subtotal,
            "total" => $this->total,
            "tax" => $this->tax,
            "paid" => $this->paid,
            "attempted" => $this->attempted,
            "attempt_count" => $this->attemptCount,
            "hosted_invoice_url" => $this->hostedInvoiceUrl,
            "invoice_pdf" => $this->invoicePdf,
            "payment_intent" => $this->paymentIntent,
            "created" => self::carbonToTimestamp($this->created),
            "due_date" => self::carbonToTimestamp($this->dueDate),
            "period_start" => self::carbonToTimestamp($this->periodStart),
            "period_end" => self::carbonToTimestamp($this->periodEnd),
            "lines" => $lines,
            "metadata" => $this->metadata,
        ];

        return Arr::whereNotNull($array);
    }

    protected function getReadOnlyFields(): array
    {
        return [
            "id",
            "number",
            "status",
            "created",
            "amount_due",
            "amount_paid",
            "amount_remaining",
            "subtotal",
            "total",
            "tax",
            "paid",
            "attempted",
            "attempt_count",
            "hosted_invoice_url",
            "invoice_pdf",
            "lines",
        ];
    }

    public function withId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function id(): ?string
    {
        return $this->id;
    }

    public function withNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function number(): ?string
    {
        return $this->number;
    }

    public function withCustomer(string $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function customer(): ?string
    {
        return $this->customer;
    }

    public function withSubscription(string $subscription): self
    {
        $this->subscription = $subscription;

        return $this;
    }

    public function subscription(): ?string
    {
        return $this->subscription;
    }

    public function withStatus(InvoiceStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function status(): ?InvoiceStatus
    {
        return $this->status;
    }

    public function withBillingReason(InvoiceBillingReason $billingReason): self
    {
        $this->billingReason = $billingReason;

        return $this;
    }

    public function billingReason(): ?InvoiceBillingReason
    {
        return $this->billingReason;
    }

    public function withCollectionMethod(CollectionMethod $collectionMethod): self
    {
        $this->collectionMethod = $collectionMethod;

        return $this;
    }

    public function collectionMethod(): ?CollectionMethod
    {
        return $this->collectionMethod;
    }

    public function withCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function currency(): ?string
    {
        return $this->currency;
    }

    public function withAmountDue(int $amountDue): self
    {
        $this->amountDue = $amountDue;

        return $this;
    }

    public function amountDue(): ?int
    {
        return $this->amountDue;
    }

    public function withAmountPaid(int $amountPaid): self
    {
        $this->amountPaid = $amountPaid;

        return $this;
    }

    public function amountPaid(): ?int
    {
        return $this->amountPaid;
    }

    public function withAmountRemaining(int $amountRemaining): self
    {
        $this->amountRemaining = $amountRemaining;

        return $this;
    }

    public function amountRemaining(): ?int
    {
        return $this->amountRemaining;
    }

    public function withSubtotal(int $subtotal): self
    {
        $this->subtotal = $subtotal;

        return $this;
    }

    public function subtotal(): ?int
    {
        return $this->subtotal;
    }

    public function withTotal(int $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function total(): ?int
    {
        return $this->total;
    }

    public function withTax(int $tax): self
    {
        $this->tax = $tax;

        return $this;
    }

    public function tax(): ?int
    {
        return $this->tax;
    }

    public function withPaid(bool $paid): self
    {
        $this->paid = $paid;

        return $this;
    }

    public function paid(): ?bool
    {
        return $this->paid;
    }

    public function withAttempted(bool $attempted): self
    {
        $this->attempted = $attempted;

        return $this;
    }

    public function attempted(): ?bool
    {
        return $this->attempted;
    }

    public function withAttemptCount(int $attemptCount): self
    {
        $this->attemptCount = $attemptCount;

        return $this;
    }

    public function attemptCount(): ?int
    {
        return $this->attemptCount;
    }

    public function withHostedInvoiceUrl(string $hostedInvoiceUrl): self
    {
        $this->hostedInvoiceUrl = $hostedInvoiceUrl;

        return $this;
    }

    public function hostedInvoiceUrl(): ?string
    {
        return $this->hostedInvoiceUrl;
    }

    public function withInvoicePdf(string $invoicePdf): self
    {
        $this->invoicePdf = $invoicePdf;

        return $this;
    }

    public function invoicePdf(): ?string
    {
        return $this->invoicePdf;
    }

    public function withPaymentIntent(string $paymentIntent): self
    {
        $this->paymentIntent = $paymentIntent;

        return $this;
    }

    public function paymentIntent(): ?string
    {
        return $this->paymentIntent;
    }

    public function withCreated(CarbonImmutable $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function created(): ?CarbonImmutable
    {
        return $this->created;
    }

    public function withDueDate(CarbonImmutable $dueDate): self
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function dueDate(): ?CarbonImmutable
    {
        return $this->dueDate;
    }

    public function withPeriodStart(CarbonImmutable $periodStart): self
    {
        $this->periodStart = $periodStart;

        return $this;
    }

    public function periodStart(): ?CarbonImmutable
    {
        return $this->periodStart;
    }

    public function withPeriodEnd(CarbonImmutable $periodEnd): self
    {
        $this->periodEnd = $periodEnd;

        return $this;
    }

    public function periodEnd(): ?CarbonImmutable
    {
        return $this->periodEnd;
    }

    /** @param Collection<int, StripeInvoiceLineItem> $lines */
    public function withLines(Collection $lines): self
    {
        $this->lines = $lines;

        return $this;
    }

    /** @return ?Collection<int, StripeInvoiceLineItem> */
    public function lines(): ?Collection
    {
        return $this->lines;
    }
}
