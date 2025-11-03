<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Support\Traits;

/** @internal */
trait HasMetadata
{
    private ?array $metadata = null;

    /** Convert Stripe metadata object to array */
    protected static function extractMetadata(object $stripeObject): ?array
    {
        if (!isset($stripeObject->metadata)) {
            return null;
        }

        $metadataJson = json_encode($stripeObject->metadata);

        return $metadataJson !== false ? json_decode($metadataJson, true) : null;
    }

    public function withMetadata(?array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function metadata(): ?array
    {
        return $this->metadata;
    }
}
