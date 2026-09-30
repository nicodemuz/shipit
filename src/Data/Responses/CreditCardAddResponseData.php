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
 * Response from initiating the credit-card add flow.
 *
 * Contains a Stripe Checkout URL the merchant should be redirected to.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class CreditCardAddResponseData extends Data
{
    /**
     * @param string $redirect Stripe Checkout URL for adding a credit card
     */
    public function __construct(
        public readonly string $redirect,
    ) {}
}
