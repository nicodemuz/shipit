<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shipit\Requests\CreditCard;

use Cline\Shipit\Data\Responses\CreditCardAddResponseData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Initiates the Stripe credit-card add flow for the authenticated merchant.
 *
 * Must be called with the merchant's own API credentials. Returns a redirect
 * URL; after Stripe completes, the user is sent to {@see $returnUrl} with
 * `event=credit_card.added`.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class AddCreditCardRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * @param string $returnUrl Absolute URL to redirect to after Stripe Checkout
     */
    public function __construct(
        private readonly string $returnUrl,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/v1/credit-card/add';
    }

    public function createDtoFromResponse(Response $response): CreditCardAddResponseData
    {
        return CreditCardAddResponseData::from($response->json());
    }

    /**
     * @return array<string, string>
     */
    protected function defaultBody(): array
    {
        return [
            'returnUrl' => $this->returnUrl,
        ];
    }
}
