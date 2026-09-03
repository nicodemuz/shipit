<?php

declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shipit\Auth;

use Saloon\Contracts\Authenticator;
use Saloon\Http\PendingRequest;

/**
 * Shipit.fi merchant authentication via X-SHIPIT-KEY and X-SHIPIT-SECRET.
 *
 * HTTP Basic is not accepted by the API (it returns "Key is not specified").
 */
final class ShipitKeySecretAuthenticator implements Authenticator
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $apiSecret,
    ) {}

    public function set(PendingRequest $pendingRequest): void
    {
        $pendingRequest->headers()->merge([
            'X-SHIPIT-KEY' => $this->apiKey,
            'X-SHIPIT-SECRET' => $this->apiSecret,
        ]);
    }
}
