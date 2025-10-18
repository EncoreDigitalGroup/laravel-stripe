<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Webhook;

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Support\HasTimestamps;
use PHPGenesis\Common\Traits\HasMake;

class StripeInvoiceWebhookData
{
    use HasMake;
    use HasTimestamps;

    /**
     * @param array<StripeInvoiceLineItem> $lines
     */
    public function __construct(
        public ?string          $id = null,
        public ?string          $number = null,
        public ?string          $subscription = null,
        public ?string          $paymentIntent = null,
        public ?string          $customer = null,
        public ?int             $subtotal = null,
        public ?int             $tax = null,
        public ?int             $total = null,
        public ?int             $amountDue = null,
        public ?int             $amountPaid = null,
        public ?int             $amountRemaining = null,
        public ?string          $status = null,
        public ?string          $currency = null,
        public ?CarbonImmutable $created = null,
        public ?CarbonImmutable $dueDate = null,
        public ?array           $lines = null,
        public ?array           $metadata = null
    ) {}

    /**
     * Create a StripeInvoiceWebhookData instance from a Stripe webhook invoice array
     */
    public static function fromWebhookData(array $invoice): self
    {
        $lines = [];
        if (isset($invoice["lines"]["data"]) && is_array($invoice["lines"]["data"])) {
            foreach ($invoice["lines"]["data"] as $lineItem) {
                $lines[] = StripeInvoiceLineItem::fromWebhookData($lineItem);
            }
        }

        return self::make(
            id: $invoice["id"] ?? null,
            number: $invoice["number"] ?? null,
            subscription: $invoice["subscription"] ?? null,
            paymentIntent: $invoice["payment_intent"] ?? null,
            customer: $invoice["customer"] ?? null,
            subtotal: $invoice["subtotal"] ?? null,
            tax: $invoice["tax"] ?? null,
            total: $invoice["total"] ?? null,
            amountDue: $invoice["amount_due"] ?? null,
            amountPaid: $invoice["amount_paid"] ?? null,
            amountRemaining: $invoice["amount_remaining"] ?? null,
            status: $invoice["status"] ?? null,
            currency: $invoice["currency"] ?? null,
            created: self::timestampToCarbon($invoice["created"] ?? null),
            dueDate: self::timestampToCarbon($invoice["due_date"] ?? null),
            lines: $lines,
            metadata: $invoice["metadata"] ?? null
        );
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
            "lines" => $this->lines ? array_map(fn($line) => $line->toArray(), $this->lines) : null,
            "metadata" => $this->metadata,
        ];

        return Arr::whereNotNull($array);
    }
}