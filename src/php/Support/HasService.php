<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Support;

use EncoreDigitalGroup\StdLib\Exceptions\NotImplementedException;

trait HasService
{
    public function service(): mixed
    {
        throw new NotImplementedException;
    }
}
