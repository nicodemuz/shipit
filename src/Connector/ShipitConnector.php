<?php

declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shipit\Connector;

use Cline\Shipit\Resources\AgentsResource;
use Cline\Shipit\Resources\BalanceResource;
use Cline\Shipit\Resources\CarrierContractsResource;
use Cline\Shipit\Resources\ConsignmentTemplatesResource;
use Cline\Shipit\Resources\LocationsResource;
use Cline\Shipit\Resources\OrganizationMembersResource;
use Cline\Shipit\Resources\OrganizationsResource;
use Cline\Shipit\Resources\PostalCodesResource;
use Cline\Shipit\Resources\ShipmentsResource;
use Cline\Shipit\Resources\ShippingMethodsResource;
use Cline\Shipit\Resources\TrackingResource;
use Cline\Shipit\Resources\UserResource;
use Cline\Shipit\Auth\ShipitKeySecretAuthenticator;
use Saloon\Contracts\Authenticator;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\Response;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;

/**
 * Main connector for the Shipit API.
 *
 * Framework-agnostic Saloon connector. Works with Symfony, Laravel, or plain PHP.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class ShipitConnector extends Connector
{
    use AcceptsJson;
    use AlwaysThrowOnErrors;

    public const string LIVE_BASE_URL = 'https://api.shipit.fi';

    public const string TEST_BASE_URL = 'https://apitest.shipit.ax';

    private string $apiBaseUrl;

    /**
     * @param string        $baseUrl API base URL (trailing slash optional)
     * @param Authenticator $auth    Shipit.fi key+secret headers or Bearer token
     */
    public function __construct(string $baseUrl, Authenticator $auth)
    {
        $this->apiBaseUrl = rtrim($baseUrl, '/');
        $this->authenticate($auth);
    }

    /**
     * Create a connector with Shipit.fi merchant authentication (API key + secret headers).
     *
     * Defaults to the live API. Pass {@see TEST_BASE_URL} (`https://apitest.shipit.ax`) for non-production.
     */
    public static function basic(
        string $apiKey,
        string $apiSecret,
        string $baseUrl = self::LIVE_BASE_URL,
    ): self {
        return new self($baseUrl, new ShipitKeySecretAuthenticator($apiKey, $apiSecret));
    }

    /**
     * Create a connector with Bearer token authentication.
     */
    public static function token(
        string $apiToken,
        string $baseUrl = self::LIVE_BASE_URL,
    ): self {
        return new self($baseUrl, new TokenAuthenticator($apiToken));
    }

    /**
     * Production API with Bearer token (legacy helper).
     *
     * Prefer {@see basic()} for Shipit.fi merchant credentials.
     */
    public static function live(string $apiToken): self
    {
        return self::token($apiToken, self::LIVE_BASE_URL);
    }

    /**
     * Test API with Bearer token (legacy helper).
     */
    public static function test(string $apiToken): self
    {
        return self::token($apiToken, self::TEST_BASE_URL);
    }

    /**
     * Alias of {@see live()} kept for backward compatibility.
     *
     * @deprecated Use basic() or token() and choose the base URL explicitly.
     */
    public static function new(string $apiToken): self
    {
        return self::live($apiToken);
    }

    public function resolveBaseUrl(): string
    {
        return $this->apiBaseUrl;
    }

    public function hasRequestFailed(Response $response): bool
    {
        return $response->status() >= 400;
    }

    public function shippingMethods(): ShippingMethodsResource
    {
        return new ShippingMethodsResource($this);
    }

    public function shipments(): ShipmentsResource
    {
        return new ShipmentsResource($this);
    }

    public function balance(): BalanceResource
    {
        return new BalanceResource($this);
    }

    public function agents(): AgentsResource
    {
        return new AgentsResource($this);
    }

    public function locations(): LocationsResource
    {
        return new LocationsResource($this);
    }

    public function organizations(): OrganizationsResource
    {
        return new OrganizationsResource($this);
    }

    public function postalCodes(): PostalCodesResource
    {
        return new PostalCodesResource($this);
    }

    public function tracking(): TrackingResource
    {
        return new TrackingResource($this);
    }

    public function user(): UserResource
    {
        return new UserResource($this);
    }

    public function carrierContracts(): CarrierContractsResource
    {
        return new CarrierContractsResource($this);
    }

    public function consignmentTemplates(): ConsignmentTemplatesResource
    {
        return new ConsignmentTemplatesResource($this);
    }

    public function organizationMembers(): OrganizationMembersResource
    {
        return new OrganizationMembersResource($this);
    }

    /**
     * @return array<string, string>
     */
    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }
}
