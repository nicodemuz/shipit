<?php

declare(strict_types=1);

namespace Cline\Shipit\Dto;

/**
 * Sentinel value meaning "omit this property from serialization".
 */
final class Optional
{
    public static function create(): self
    {
        return new self();
    }
}
