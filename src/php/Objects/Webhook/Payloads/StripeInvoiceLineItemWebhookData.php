<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Webhook\Payloads;

use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Support\Traits\HasIdentifier;
use EncoreDigitalGroup\Stripe\Support\Traits\HasMetadata;
use PHPGenesis\Common\Traits\HasMake;

class StripeInvoiceLineItemWebhookData implements IWebhookData
{
    use HasIdentifier;
    use HasMake;
    use HasMetadata;

    private ?string $description = null;
    private ?int $amount = null;
    private ?int $quantity = null;
    private ?int $unitAmount = null;
    private ?string $priceId = null;
    private ?string $productId = null;
    private ?array $price = null;

    /**
     * Create a StripeInvoiceLineItem instance from a Stripe InvoiceLineItem object
     */
    public static function fromStripeObject(object $lineItem): self
    {
        $priceId = null;
        $productId = null;
        $priceArray = null;

        if (isset($lineItem->price)) {
            $priceId = is_string($lineItem->price->id ?? null) ? $lineItem->price->id : null;
            $productId = is_string($lineItem->price->product ?? null) ? $lineItem->price->product : null;
            $priceJson = json_encode($lineItem->price);
            $priceArray = $priceJson !== false ? json_decode($priceJson, true) : null;
        }

        return self::make()
            ->withId($lineItem->id ?? null)
            ->withDescription($lineItem->description ?? null)
            ->withAmount($lineItem->amount ?? null)
            ->withQuantity($lineItem->quantity ?? null)
            ->withUnitAmount($lineItem->unit_amount ?? null)
            ->withPriceId($priceId)
            ->withProductId($productId)
            ->withPrice($priceArray)
            ->withMetadata(self::extractMetadata($lineItem));
    }

    public function withDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function withAmount(?int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function amount(): ?int
    {
        return $this->amount;
    }

    public function withQuantity(?int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function quantity(): ?int
    {
        return $this->quantity;
    }

    public function withUnitAmount(?int $unitAmount): self
    {
        $this->unitAmount = $unitAmount;

        return $this;
    }

    public function unitAmount(): ?int
    {
        return $this->unitAmount;
    }

    public function withPriceId(?string $priceId): self
    {
        $this->priceId = $priceId;

        return $this;
    }

    public function priceId(): ?string
    {
        return $this->priceId;
    }

    public function withProductId(?string $productId): self
    {
        $this->productId = $productId;

        return $this;
    }

    public function productId(): ?string
    {
        return $this->productId;
    }

    public function withPrice(?array $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function price(): ?array
    {
        return $this->price;
    }

    public function toArray(): array
    {
        $array = [
            "id" => $this->id,
            "description" => $this->description,
            "amount" => $this->amount,
            "quantity" => $this->quantity,
            "unit_amount" => $this->unitAmount,
            "price_id" => $this->priceId,
            "product_id" => $this->productId,
            "price" => $this->price,
            "metadata" => $this->metadata,
        ];

        return Arr::whereNotNull($array);
    }
}