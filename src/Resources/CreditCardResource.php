<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shipit\Resources;

use Cline\Shipit\Data\Responses\CreditCardAddResponseData;
use Cline\Shipit\Data\Responses\CreditCardHasResponseData;
use Cline\Shipit\Data\Responses\CreditCardRemoveResponseData;
use Cline\Shipit\Requests\CreditCard\AddCreditCardRequest;
use Cline\Shipit\Requests\CreditCard\HasCreditCardRequest;
use Cline\Shipit\Requests\CreditCard\RemoveCreditCardRequest;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\BaseResource;

/**
 * Credit card resource for Shipit wallet top-up payment methods.
 *
 * These endpoints must be called with the **merchant user's** API key and
 * secret (not a platform/partner key). Cards are stored with Stripe; Shipit
 * only orchestrates the Checkout redirect and auto top-up when the wallet
 * balance drops below 15%.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @see https://apidocs.shipit.ax/
 */
final class CreditCardResource extends BaseResource
{
    /**
     * Start the Stripe flow for adding a credit card.
     *
     * @param string $returnUrl Absolute URL; Stripe redirects here with `event=credit_card.added`
     *
     * @throws FatalRequestException
     * @throws RequestException
     *
     * @return CreditCardAddResponseData Contains the Stripe Checkout redirect URL
     */
    public function add(string $returnUrl): CreditCardAddResponseData
    {
        /** @var CreditCardAddResponseData */
        return $this->connector
            ->send(new AddCreditCardRequest($returnUrl))
            ->dtoOrFail();
    }

    /**
     * Check whether the authenticated user has a credit card configured.
     *
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function has(): CreditCardHasResponseData
    {
        /** @var CreditCardHasResponseData */
        return $this->connector
            ->send(new HasCreditCardRequest())
            ->dtoOrFail();
    }

    /**
     * Remove the credit card from the authenticated user account.
     *
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function remove(): CreditCardRemoveResponseData
    {
        /** @var CreditCardRemoveResponseData */
        return $this->connector
            ->send(new RemoveCreditCardRequest())
            ->dtoOrFail();
    }
}
