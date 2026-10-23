<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\Serialization;

use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class SerializerFactory
{
    public static function create(): Serializer
    {
        $types = new PropertyInfoExtractor(typeExtractors: [
            new PhpDocExtractor(),
            new ReflectionExtractor(),
        ]);

        return new Serializer([
            new ArrayDenormalizer(),
            new BackedEnumNormalizer(),
            new ObjectNormalizer(propertyTypeExtractor: $types),
        ]);
    }
}
