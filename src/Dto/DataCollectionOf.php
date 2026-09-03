<?php

declare(strict_types=1);

namespace Cline\Shipit\Dto;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final class DataCollectionOf
{
    /**
     * @param class-string $class
     */
    public function __construct(
        public readonly string $class,
    ) {}
}
