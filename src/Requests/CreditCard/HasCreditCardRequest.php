<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shipit\Requests\CreditCard;

use Cline\Shipit\Data\Responses\CreditCardHasResponseData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * Checks whether the authenticated merchant has a credit card configured.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class HasCreditCardRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/v1/credit-card/has';
    }

    public function createDtoFromResponse(Response $response): CreditCardHasResponseData
    {
        return CreditCardHasResponseData::from($response->json());
    }
}
