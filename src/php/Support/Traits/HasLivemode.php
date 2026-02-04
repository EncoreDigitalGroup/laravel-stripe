<?php

namespace EncoreDigitalGroup\Stripe\Support\Traits;

/** @internal */
trait HasLivemode
{
    private ?bool $livemode = null;

    public function withLivemode(?bool $livemode): self
    {
        $this->livemode = $livemode;

        return $this;
    }

    public function livemode(): ?bool
    {
        return $this->livemode;
    }
}
