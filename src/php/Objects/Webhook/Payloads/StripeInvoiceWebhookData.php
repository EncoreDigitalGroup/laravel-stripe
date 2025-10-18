<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Webhook\Payloads;

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Support\HasIdentifier;
use EncoreDigitalGroup\Stripe\Support\HasMetadata;
use EncoreDigitalGroup\Stripe\Support\HasTimestamps;
use PHPGenesis\Common\Traits\HasMake;

class StripeInvoiceWebhookData
{
    use HasIdentifier;
    use HasMake;
    use HasMetadata;
    use HasTimestamps;

    private ?string $number = null;
    private ?string $subscription = null;
    private ?string $paymentIntent = null;
    private ?string $customer = null;
    private ?int $subtotal = null;
    private ?int $tax = null;
    private ?int $total = null;
    private ?int $amountDue = null;
    private ?int $amountPaid = null;
    private ?int $amountRemaining = null;
    private ?string $status = null;
    private ?string $currency = null;
    private ?CarbonImmutable $created = null;
    private ?CarbonImmutable $dueDate = null;

    /**
     * @var array<StripeInvoiceLineItemWebhookData>|null
     */
    private ?array $lines = null;

    /**
     * Create a StripeInvoiceWebhookData instance from a Stripe Invoice object
     */
    public static function fromStripeObject(object $invoice): self
    {
        $lines = [];
        if (isset($invoice->lines->data) && is_array($invoice->lines->data)) {
            foreach ($invoice->lines->data as $lineItem) {
                $lines[] = StripeInvoiceLineItemWebhookData::fromStripeObject($lineItem);
            }
        }

        return self::make()
            ->withId($invoice->id ?? null)
            ->withNumber($invoice->number ?? null)
            ->withSubscription($invoice->subscription ?? null)
            ->withPaymentIntent($invoice->payment_intent ?? null)
            ->withCustomer($invoice->customer ?? null)
            ->withSubtotal($invoice->subtotal ?? null)
            ->withTax($invoice->tax ?? null)
            ->withTotal($invoice->total ?? null)
            ->withAmountDue($invoice->amount_due ?? null)
            ->withAmountPaid($invoice->amount_paid ?? null)
            ->withAmountRemaining($invoice->amount_remaining ?? null)
            ->withStatus($invoice->status ?? null)
            ->withCurrency($invoice->currency ?? null)
            ->withCreated(self::timestampToCarbon($invoice->created ?? null))
            ->withDueDate(self::timestampToCarbon($invoice->due_date ?? null))
            ->withLines($lines)
            ->withMetadata(self::extractMetadata($invoice));
    }

    public function withNumber(?string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function number(): ?string
    {
        return $this->number;
    }

    public function withSubscription(?string $subscription): self
    {
        $this->subscription = $subscription;

        return $this;
    }

    public function subscription(): ?string
    {
        return $this->subscription;
    }

    public function withPaymentIntent(?string $paymentIntent): self
    {
        $this->paymentIntent = $paymentIntent;

        return $this;
    }

    public function paymentIntent(): ?string
    {
        return $this->paymentIntent;
    }

    public function withCustomer(?string $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function customer(): ?string
    {
        return $this->customer;
    }

    public function withSubtotal(?int $subtotal): self
    {
        $this->subtotal = $subtotal;

        return $this;
    }

    public function subtotal(): ?int
    {
        return $this->subtotal;
    }

    public function withTax(?int $tax): self
    {
        $this->tax = $tax;

        return $this;
    }

    public function tax(): ?int
    {
        return $this->tax;
    }

    public function withTotal(?int $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function total(): ?int
    {
        return $this->total;
    }

    public function withAmountDue(?int $amountDue): self
    {
        $this->amountDue = $amountDue;

        return $this;
    }

    public function amountDue(): ?int
    {
        return $this->amountDue;
    }

    public function withAmountPaid(?int $amountPaid): self
    {
        $this->amountPaid = $amountPaid;

        return $this;
    }

    public function amountPaid(): ?int
    {
        return $this->amountPaid;
    }

    public function withAmountRemaining(?int $amountRemaining): self
    {
        $this->amountRemaining = $amountRemaining;

        return $this;
    }

    public function amountRemaining(): ?int
    {
        return $this->amountRemaining;
    }

    public function withStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function status(): ?string
    {
        return $this->status;
    }

    public function withCurrency(?string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function currency(): ?string
    {
        return $this->currency;
    }

    public function withCreated(?CarbonImmutable $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function created(): ?CarbonImmutable
    {
        return $this->created;
    }

    public function withDueDate(?CarbonImmutable $dueDate): self
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function dueDate(): ?CarbonImmutable
    {
        return $this->dueDate;
    }

    public function withLines(?array $lines): self
    {
        $this->lines = $lines;

        return $this;
    }

    public function lines(): ?array
    {
        return $this->lines;
    }

    public function toArray(): array
    {
        $array = [
            "id" => $this->id,
            "number" => $this->number,
            "subscription" => $this->subscription,
            "payment_intent" => $this->paymentIntent,
            "customer" => $this->customer,
            "subtotal" => $this->subtotal,
            "tax" => $this->tax,
            "total" => $this->total,
            "amount_due" => $this->amountDue,
            "amount_paid" => $this->amountPaid,
            "amount_remaining" => $this->amountRemaining,
            "status" => $this->status,
            "currency" => $this->currency,
            "created" => self::carbonToTimestamp($this->created),
            "due_date" => self::carbonToTimestamp($this->dueDate),
            "lines" => $this->lines !== null && $this->lines !== [] ? array_map(fn ($line): array => $line->toArray(), $this->lines) : null,
            "metadata" => $this->metadata,
        ];

        return Arr::whereNotNull($array);
    }
}