<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Support;

/** @internal */
trait HasSave
{
    use HasService;

    public function save(): self
    {
        return is_null($this->id) ? $this->service()->create($this) : $this->service()->update($this->id, $this);
    }
}
