<?php
/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe\Objects\Support\Normalizers;

use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class AbstractNormalizer
{
    public function __construct(protected ObjectNormalizer $objectNormalizer = new ObjectNormalizer) {}
}