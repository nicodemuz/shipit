<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shipit\Requests\CreditCard;

use Cline\Shipit\Data\Responses\CreditCardRemoveResponseData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * Removes the credit card from the authenticated merchant account.
 *
 * Must be called with the merchant's own API credentials. No request body.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class RemoveCreditCardRequest extends Request
{
    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/v1/credit-card/remove';
    }

    public function createDtoFromResponse(Response $response): CreditCardRemoveResponseData
    {
        return CreditCardRemoveResponseData::from($response->json());
    }
}
