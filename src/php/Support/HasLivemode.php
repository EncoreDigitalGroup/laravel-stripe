<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Support;

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
