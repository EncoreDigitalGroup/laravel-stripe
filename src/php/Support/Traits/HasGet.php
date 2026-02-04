<?php

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
