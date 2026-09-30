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
 * Response from removing a credit card from the authenticated user account.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class CreditCardRemoveResponseData extends Data
{
    /**
     * @param int|Optional    $status  Shipit status flag (1 = success)
     * @param string|Optional $message Human-readable result message
     */
    public function __construct(
        public readonly int|Optional $status,
        public readonly string|Optional $message,
    ) {}

    public function isSuccess(): bool
    {
        return $this->status === 1;
    }
}
