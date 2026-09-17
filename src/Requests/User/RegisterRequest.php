<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shipit\Requests\User;

use Cline\Shipit\Data\RegistrationRequestData;
use Cline\Shipit\Data\Responses\RegistrationResponseData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Registers a new merchant account on Shipit.fi.
 *
 * Creates a merchant account and returns API credentials (key/secret)
 * for subsequent authenticated API calls.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class RegisterRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    /**
     * @param RegistrationRequestData $data Merchant registration details
     */
    public function __construct(
        private readonly RegistrationRequestData $data,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/v1/register';
    }

    public function createDtoFromResponse(Response $response): RegistrationResponseData
    {
        $payload = $response->json();

        return RegistrationResponseData::from(is_array($payload) ? $payload : []);
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        /** @var array<string, mixed> */
        return $this->data->toArray();
    }
}
