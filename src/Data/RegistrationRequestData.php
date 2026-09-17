<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shipit\Data;

use Cline\Shipit\Dto\Data;
use Cline\Shipit\Dto\Optional;

/**
 * Merchant registration request for Shipit.fi (`PUT /v1/register`).
 *
 * Creates a merchant account and returns API credentials (key/secret).
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class RegistrationRequestData extends Data
{
    /**
     * @param string          $name                 Merchant or company display name
     * @param string          $email                Account email address
     * @param string          $phone                Contact phone number
     * @param string          $address              Street address
     * @param string          $postcode             Postal code
     * @param string          $city                 City
     * @param string          $country              ISO 3166-1 alpha-2 country code
     * @param Optional|string $state                State or region (optional)
     * @param bool|Optional   $isCompany            Whether the merchant is a company
     * @param Optional|string $contactPerson        Primary contact person name
     * @param Optional|string $businessId           Business ID / Y-tunnus
     * @param Optional|string $eori                 EORI number for customs
     * @param bool|Optional   $subscribeNewsletter  Newsletter opt-in
     */
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $address,
        public readonly string $postcode,
        public readonly string $city,
        public readonly string $country,
        public readonly string|Optional $state,
        public readonly bool|Optional $isCompany,
        public readonly string|Optional $contactPerson,
        public readonly string|Optional $businessId,
        public readonly string|Optional $eori,
        public readonly bool|Optional $subscribeNewsletter,
    ) {}
}
