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
 * Pickup / service-point location returned with shipping-method quotes.
 *
 * Field names match the Shipit.fi shipping-methods `locations` payload
 * (`address1`, `zipcode`, `countryCode`, …).
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class ServicePointLocationData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $address1,
        public readonly string $zipcode,
        public readonly string $city,
        public readonly string $countryCode,
        public readonly string $serviceId,
        public readonly string $carrier,
        public readonly float|Optional $price,
        public readonly string|Optional $carrierLogo,
        public readonly float|Optional $latitude,
        public readonly float|Optional $longitude,
        public readonly array|Optional $openingHours,
        public readonly string|Optional $distance,
    ) {}
}
