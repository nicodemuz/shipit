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
 * Merchant registration response from Shipit.fi.
 *
 * On success, `credentials` contains the new merchant API key and secret.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class RegistrationResponseData extends Data
{
    /**
     * @param RegistrationCredentialsData|Optional $credentials New merchant API credentials
     * @param array<string, mixed>|Optional         $error       Structured error payload when registration fails
     * @param array<int, string>|Optional           $errorbag    Validation error messages
     * @param mixed                                 $payload     Raw extra payload from the API (when present)
     */
    public function __construct(
        public readonly RegistrationCredentialsData|Optional $credentials,
        public readonly array|Optional $error,
        public readonly array|Optional $errorbag,
        public readonly mixed $payload = null,
    ) {}

    public function hasError(): bool
    {
        return !$this->error instanceof Optional;
    }

    public function hasCredentials(): bool
    {
        return $this->credentials instanceof RegistrationCredentialsData;
    }
}
