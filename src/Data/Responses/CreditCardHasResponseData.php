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
 * Response indicating whether the authenticated user has a credit card configured.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class CreditCardHasResponseData extends Data
{
    /**
     * @param bool $hasCreditCard Whether a credit card is configured on the account
     */
    public function __construct(
        public readonly bool $hasCreditCard,
    ) {}
}
