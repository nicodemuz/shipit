<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shipit\Data\Responses;

use Cline\Shipit\Dto\Data;
use Cline\Shipit\Dto\Optional;

/**
 * Catalog entry from `GET /v1/list-methods`.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class ListedShippingMethodData extends Data
{
    public function __construct(
        public readonly string $serviceId,
        public readonly string $name,
        public readonly string $carrier,
        public readonly bool $domesticDeliveries,
        public readonly bool $homeDelivery,
        public readonly bool $pickUpPoints,
        public readonly string|Optional $logo,
    ) {}
}
