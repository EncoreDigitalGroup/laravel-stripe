<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Support;

/** @internal */
trait HasIdentifier
{
    private ?string $id = null;

    public function withId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function id(): ?string
    {
        return $this->id;
    }
}
