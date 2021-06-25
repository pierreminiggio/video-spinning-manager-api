<?php

namespace App\Serializer;

interface SerializerInterface
{
    public function serialize(mixed $entity): string;
}