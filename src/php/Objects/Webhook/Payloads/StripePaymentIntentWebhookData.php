<?php

namespace EncoreDigitalGroup\Stripe\Objects\Webhook\Payloads;

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Support\Traits\HasIdentifier;
use EncoreDigitalGroup\Stripe\Support\Traits\HasMetadata;
use EncoreDigitalGroup\Stripe\Support\Traits\HasTimestamps;
use PHPGenesis\Common\Traits\HasMake;

class StripePaymentIntentWebhookData implements IWebhookData
{
    use HasIdentifier;
    use HasMake;
    use HasMetadata;
    use HasTimestamps;

    private ?string $status = null;
    private ?int $amount = null;
    private ?int $amountReceived = null;
    private ?string $currency = null;
    private ?string $customer = null;
    private ?string $invoice = null;
    private ?string $paymentMethod = null;
    private ?string $description = null;
    private ?string $cancellationReason = null;
    private ?array $lastPaymentError = null;
    private ?CarbonImmutable $created = null;

    /** Create a StripePaymentIntentWebhookData instance from a Stripe PaymentIntent object */
    public static function fromStripeObject(object $paymentIntent): self
    {
        $lastPaymentError = null;
        if (isset($paymentIntent->last_payment_error)) {
            $errorJson = json_encode($paymentIntent->last_payment_error);
            $lastPaymentError = $errorJson !== false ? json_decode($errorJson, true) : null;
        }

        return self::make()
            ->withId($paymentIntent->id ?? null)
            ->withStatus($paymentIntent->status ?? null)
            ->withAmount($paymentIntent->amount ?? null)
            ->withAmountReceived($paymentIntent->amount_received ?? null)
            ->withCurrency($paymentIntent->currency ?? null)
            ->withCustomer($paymentIntent->customer ?? null)
            ->withInvoice($paymentIntent->invoice ?? null)
            ->withPaymentMethod($paymentIntent->payment_method ?? null)
            ->withDescription($paymentIntent->description ?? null)
            ->withCancellationReason($paymentIntent->cancellation_reason ?? null)
            ->withLastPaymentError($lastPaymentError)
            ->withCreated(self::timestampToCarbon($paymentIntent->created ?? null))
            ->withMetadata(self::extractMetadata($paymentIntent));
    }

    public function withCreated(?CarbonImmutable $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function withLastPaymentError(?array $lastPaymentError): self
    {
        $this->lastPaymentError = $lastPaymentError;

        return $this;
    }

    public function withCancellationReason(?string $cancellationReason): self
    {
        $this->cancellationReason = $cancellationReason;

        return $this;
    }

    public function withDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function withPaymentMethod(?string $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function withInvoice(?string $invoice): self
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function withCustomer(?string $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function withCurrency(?string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function withAmountReceived(?int $amountReceived): self
    {
        $this->amountReceived = $amountReceived;

        return $this;
    }

    public function withAmount(?int $amount): self
    {
        $this->amount = $amount;

        return $this;
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

    public function amount(): ?int
    {
        return $this->amount;
    }

    public function amountReceived(): ?int
    {
        return $this->amountReceived;
    }

    public function currency(): ?string
    {
        return $this->currency;
    }

    public function customer(): ?string
    {
        return $this->customer;
    }

    public function invoice(): ?string
    {
        return $this->invoice;
    }

    public function paymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function cancellationReason(): ?string
    {
        return $this->cancellationReason;
    }

    public function lastPaymentError(): ?array
    {
        return $this->lastPaymentError;
    }

    public function created(): ?CarbonImmutable
    {
        return $this->created;
    }

    public function toArray(): array
    {
        $array = [
            "id" => $this->id,
            "status" => $this->status,
            "amount" => $this->amount,
            "amount_received" => $this->amountReceived,
            "currency" => $this->currency,
            "customer" => $this->customer,
            "invoice" => $this->invoice,
            "payment_method" => $this->paymentMethod,
            "description" => $this->description,
            "cancellation_reason" => $this->cancellationReason,
            "last_payment_error" => $this->lastPaymentError,
            "created" => self::carbonToTimestamp($this->created),
            "metadata" => $this->metadata,
        ];

        return Arr::whereNotNull($array);
    }
}