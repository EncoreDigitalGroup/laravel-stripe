<?php

namespace EncoreDigitalGroup\Stripe\Support\Traits;

use EncoreDigitalGroup\StdLib\Exceptions\NotImplementedException;

/** @internal */
trait HasService
{
    public function service(): mixed
    {
        throw new NotImplementedException;
    }
}
