<?php

namespace EncoreDigitalGroup\Stripe\Support\Traits;

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
