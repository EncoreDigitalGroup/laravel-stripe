<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Payment;

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Enums\PaymentIntentCaptureMethod;
use EncoreDigitalGroup\Stripe\Enums\PaymentIntentConfirmationMethod;
use EncoreDigitalGroup\Stripe\Enums\PaymentIntentSetupFutureUsage;
use EncoreDigitalGroup\Stripe\Enums\PaymentIntentStatus;
use EncoreDigitalGroup\Stripe\Enums\PaymentMethodType;
use EncoreDigitalGroup\Stripe\Support\Traits\HasMetadata;
use EncoreDigitalGroup\Stripe\Support\Traits\HasTimestamps;
use Illuminate\Support\Collection;
use PHPGenesis\Common\Traits\HasMake;
use Stripe\PaymentIntent;

class StripePaymentIntent
{
    use HasMake;
    use HasMetadata;
    use HasTimestamps;

    private ?string $id = null;
    private ?int $amount = null;
    private ?int $amountCapturable = null;
    private ?int $amountReceived = null;
    private ?string $currency = null;
    private ?string $customer = null;
    private ?string $description = null;
    private ?string $invoice = null;
    private ?string $paymentMethod = null;
    private ?PaymentIntentStatus $status = null;
    private ?PaymentIntentCaptureMethod $captureMethod = null;
    private ?PaymentIntentConfirmationMethod $confirmationMethod = null;
    private ?PaymentIntentSetupFutureUsage $setupFutureUsage = null;
    private ?CarbonImmutable $created = null;
    private ?string $clientSecret = null;
    private ?array $lastPaymentError = null;

    /** @var ?Collection<PaymentMethodType> */
    private ?Collection $paymentMethodTypes = null;

    public static function fromStripeObject(PaymentIntent $paymentIntent): self
    {
        $instance = self::make();

        $instance = self::setBasicProperties($instance, $paymentIntent);
        $instance = self::setEnumProperties($instance, $paymentIntent);
        $instance = self::setRelationProperties($instance, $paymentIntent);

        return self::setAdditionalProperties($instance, $paymentIntent);
    }

    private static function setBasicProperties(self $instance, PaymentIntent $paymentIntent): self
    {
        if ($paymentIntent->id) {
            $instance = $instance->withId($paymentIntent->id);
        }

        if (isset($paymentIntent->amount)) {
            $instance = $instance->withAmount($paymentIntent->amount);
        }

        if (isset($paymentIntent->amount_capturable)) {
            $instance = $instance->withAmountCapturable($paymentIntent->amount_capturable);
        }

        if (isset($paymentIntent->amount_received)) {
            $instance = $instance->withAmountReceived($paymentIntent->amount_received);
        }

        if ($paymentIntent->currency ?? null) {
            $instance = $instance->withCurrency($paymentIntent->currency);
        }

        if ($paymentIntent->description ?? null) {
            $instance = $instance->withDescription($paymentIntent->description);
        }

        if ($paymentIntent->client_secret ?? null) {
            return $instance->withClientSecret($paymentIntent->client_secret);
        }

        return $instance;
    }

    private static function setEnumProperties(self $instance, PaymentIntent $paymentIntent): self
    {
        if ($paymentIntent->status ?? null) {
            $instance = $instance->withStatus(PaymentIntentStatus::from($paymentIntent->status));
        }

        if ($paymentIntent->capture_method ?? null) {
            $instance = $instance->withCaptureMethod(PaymentIntentCaptureMethod::from($paymentIntent->capture_method));
        }

        if ($paymentIntent->confirmation_method ?? null) {
            $instance = $instance->withConfirmationMethod(PaymentIntentConfirmationMethod::from($paymentIntent->confirmation_method));
        }

        if ($paymentIntent->setup_future_usage ?? null) {
            return $instance->withSetupFutureUsage(PaymentIntentSetupFutureUsage::from($paymentIntent->setup_future_usage));
        }

        return $instance;
    }

    private static function setRelationProperties(self $instance, PaymentIntent $paymentIntent): self
    {
        if ($paymentIntent->customer ?? null) {
            $customerId = is_string($paymentIntent->customer) ? $paymentIntent->customer : $paymentIntent->customer->id;
            $instance = $instance->withCustomer($customerId);
        }

        if ($paymentIntent->invoice ?? null) {
            $invoiceId = is_string($paymentIntent->invoice) ? $paymentIntent->invoice : $paymentIntent->invoice->id;
            $instance = $instance->withInvoice($invoiceId);
        }

        if ($paymentIntent->payment_method ?? null) {
            $paymentMethodId = is_string($paymentIntent->payment_method) ? $paymentIntent->payment_method : $paymentIntent->payment_method->id;
            $instance = $instance->withPaymentMethod($paymentMethodId);
        }

        return $instance;
    }

    private static function setAdditionalProperties(self $instance, PaymentIntent $paymentIntent): self
    {
        if ($paymentIntent->created ?? null) {
            $created = self::timestampToCarbon($paymentIntent->created);
            if ($created instanceof CarbonImmutable) {
                $instance = $instance->withCreated($created);
            }
        }

        if (isset($paymentIntent->last_payment_error)) {
            $errorJson = json_encode($paymentIntent->last_payment_error);
            $lastPaymentError = $errorJson !== false ? json_decode($errorJson, true) : null;
            $instance = $instance->withLastPaymentError($lastPaymentError);
        }

        if (isset($paymentIntent->payment_method_types)) {
            $paymentMethodTypes = collect($paymentIntent->payment_method_types)
                ->map(fn (string $type): PaymentMethodType => PaymentMethodType::from($type));
            $instance = $instance->withPaymentMethodTypes($paymentMethodTypes);
        }

        if (isset($paymentIntent->metadata)) {
            return $instance->withMetadata(self::extractMetadata($paymentIntent));
        }

        return $instance;
    }

    public function toArray(): array
    {
        $array = [
            "id" => $this->id,
            "amount" => $this->amount,
            "amount_capturable" => $this->amountCapturable,
            "amount_received" => $this->amountReceived,
            "currency" => $this->currency,
            "customer" => $this->customer,
            "description" => $this->description,
            "invoice" => $this->invoice,
            "payment_method" => $this->paymentMethod,
            "status" => $this->status?->value,
            "capture_method" => $this->captureMethod?->value,
            "confirmation_method" => $this->confirmationMethod?->value,
            "setup_future_usage" => $this->setupFutureUsage?->value,
            "created" => self::carbonToTimestamp($this->created),
            "client_secret" => $this->clientSecret,
            "last_payment_error" => $this->lastPaymentError,
            "payment_method_types" => $this->paymentMethodTypes?->map(fn (PaymentMethodType $type): string => $type->value)?->toArray(),
            "metadata" => $this->metadata,
        ];

        return Arr::whereNotNull($array);
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

    public function withAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function amount(): ?int
    {
        return $this->amount;
    }

    public function withAmountCapturable(int $amountCapturable): self
    {
        $this->amountCapturable = $amountCapturable;

        return $this;
    }

    public function amountCapturable(): ?int
    {
        return $this->amountCapturable;
    }

    public function withAmountReceived(int $amountReceived): self
    {
        $this->amountReceived = $amountReceived;

        return $this;
    }

    public function amountReceived(): ?int
    {
        return $this->amountReceived;
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

    public function withCustomer(string $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function customer(): ?string
    {
        return $this->customer;
    }

    public function withDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function withInvoice(string $invoice): self
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function invoice(): ?string
    {
        return $this->invoice;
    }

    public function withPaymentMethod(string $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function paymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function withStatus(PaymentIntentStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function status(): ?PaymentIntentStatus
    {
        return $this->status;
    }

    public function withCaptureMethod(PaymentIntentCaptureMethod $captureMethod): self
    {
        $this->captureMethod = $captureMethod;

        return $this;
    }

    public function captureMethod(): ?PaymentIntentCaptureMethod
    {
        return $this->captureMethod;
    }

    public function withConfirmationMethod(PaymentIntentConfirmationMethod $confirmationMethod): self
    {
        $this->confirmationMethod = $confirmationMethod;

        return $this;
    }

    public function confirmationMethod(): ?PaymentIntentConfirmationMethod
    {
        return $this->confirmationMethod;
    }

    public function withSetupFutureUsage(PaymentIntentSetupFutureUsage $setupFutureUsage): self
    {
        $this->setupFutureUsage = $setupFutureUsage;

        return $this;
    }

    public function setupFutureUsage(): ?PaymentIntentSetupFutureUsage
    {
        return $this->setupFutureUsage;
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

    public function withClientSecret(string $clientSecret): self
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    public function clientSecret(): ?string
    {
        return $this->clientSecret;
    }

    public function withLastPaymentError(?array $lastPaymentError): self
    {
        $this->lastPaymentError = $lastPaymentError;

        return $this;
    }

    public function lastPaymentError(): ?array
    {
        return $this->lastPaymentError;
    }

    /** @param Collection<int, PaymentMethodType> $paymentMethodTypes */
    public function withPaymentMethodTypes(Collection $paymentMethodTypes): self
    {
        $this->paymentMethodTypes = $paymentMethodTypes;

        return $this;
    }

    /** @return ?Collection<PaymentMethodType> */
    public function paymentMethodTypes(): ?Collection
    {
        return $this->paymentMethodTypes;
    }
}
