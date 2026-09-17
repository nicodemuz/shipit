<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shipit\Data\Responses;

use Cline\Shipit\Dto\Data;

/**
 * API credentials returned after successful merchant registration.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class RegistrationCredentialsData extends Data
{
    public function __construct(
        public readonly string $key,
        public readonly string $secret,
    ) {}
}
