<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Webhook;

use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use PHPGenesis\Common\Traits\HasMake;

class StripeInvoiceLineItem
{
    use HasMake;

    public function __construct(
        public ?string $id = null,
        public ?string $description = null,
        public ?int    $amount = null,
        public ?int    $quantity = null,
        public ?int    $unitAmount = null,
        public ?string $priceId = null,
        public ?string $productId = null,
        public ?array  $price = null,
        public ?array  $metadata = null
    ) {}

    /**
     * Create a StripeInvoiceLineItem instance from a Stripe webhook line item array
     */
    public static function fromWebhookData(array $lineItem): self
    {
        return self::make(
            id: $lineItem["id"] ?? null,
            description: $lineItem["description"] ?? null,
            amount: $lineItem["amount"] ?? null,
            quantity: $lineItem["quantity"] ?? null,
            unitAmount: $lineItem["unit_amount"] ?? null,
            priceId: is_string($lineItem["price"]["id"] ?? null) ? $lineItem["price"]["id"] : null,
            productId: is_string($lineItem["price"]["product"] ?? null) ? $lineItem["price"]["product"] : null,
            price: $lineItem["price"] ?? null,
            metadata: $lineItem["metadata"] ?? null
        );
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