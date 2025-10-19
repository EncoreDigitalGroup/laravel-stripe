<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Support\Traits;

/** @internal */
trait HasGet
{
    use HasService;

    public function get(string $id): self
    {
        return $this->service()->get($id);
    }
}
