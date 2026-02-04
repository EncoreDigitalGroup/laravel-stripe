<?php

namespace EncoreDigitalGroup\Stripe\Objects\Support\Normalizers;

use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class AbstractNormalizer
{
    public function __construct(protected ObjectNormalizer $objectNormalizer = new ObjectNormalizer) {}
}