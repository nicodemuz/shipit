<?php

declare(strict_types=1);

namespace Tests\Unit;

use Cline\Shipit\Auth\ShipitKeySecretAuthenticator;
use Cline\Shipit\Connector\ShipitConnector;
use Cline\Shipit\Data\ParcelData;
use Cline\Shipit\Data\PartyData;
use Cline\Shipit\Data\ShipmentRequestData;
use Cline\Shipit\Data\ShippingMethodsRequestData;
use Cline\Shipit\Dto\DataCollection;
use Cline\Shipit\Dto\Optional;
use Cline\Shipit\Requests\Agents\GetAgentsRequest;
use PHPUnit\Framework\Attributes\Test;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Tests\TestCase;

final class ShipitConnectorTest extends TestCase
{
    #[Test]
    public function it_builds_live_connector_with_token_auth(): void
    {
        $connector = ShipitConnector::live('token-123');

        $this->assertSame(ShipitConnector::LIVE_BASE_URL, $connector->resolveBaseUrl());
        $this->assertInstanceOf(TokenAuthenticator::class, $connector->getAuthenticator());
    }

    #[Test]
    public function it_builds_basic_auth_connector_for_shipit_fi_credentials(): void
    {
        $connector = ShipitConnector::basic('key', 'secret', 'https://api.shipit.fi');

        $this->assertSame('https://api.shipit.fi', $connector->resolveBaseUrl());
        $this->assertInstanceOf(ShipitKeySecretAuthenticator::class, $connector->getAuthenticator());
    }

    #[Test]
    public function it_maps_shipping_methods_response(): void
    {
        $connector = ShipitConnector::basic('key', 'secret');
        $connector->withMockClient(new MockClient([
            MockResponse::make([
                'status' => 200,
                'methods' => [
                    [
                        'serviceId' => 'posti.2103',
                        'carrier' => 'Posti',
                        'serviceName' => 'Posti Parcel',
                        'price' => 5.9,
                        'isPickupLocationMethod' => true,
                    ],
                ],
            ], 200),
        ]));

        $response = $connector->shippingMethods()->get(
            ShippingMethodsRequestData::from([
                'sender' => $this->party(),
                'receiver' => $this->party(),
                'parcels' => [
                    [
                        'length' => 20.0,
                        'width' => 15.0,
                        'height' => 10.0,
                        'weight' => 0.5,
                    ],
                ],
            ]),
        );

        $this->assertSame(200, $response->status);
        $this->assertCount(1, $response->methods);
        $this->assertSame('posti.2103', $response->methods[0]->serviceId);
        $this->assertTrue($response->methods[0]->isPickupLocationMethod);
    }

    #[Test]
    public function it_treats_null_optional_fields_as_omitted(): void
    {
        $connector = ShipitConnector::basic('key', 'secret', ShipitConnector::TEST_BASE_URL);
        $connector->withMockClient(new MockClient([
            MockResponse::make([
                'status' => 200,
                'methods' => [
                    [
                        'serviceId' => 'posti.po2103',
                        'carrier' => 'Posti',
                        'serviceName' => 'Postipaketti',
                        'price' => 8.14,
                        'descriptions' => null,
                        'logo' => null,
                    ],
                ],
            ], 200),
        ]));

        $response = $connector->shippingMethods()->get(
            ShippingMethodsRequestData::from([
                'sender' => $this->party(),
                'receiver' => $this->party(),
                'parcels' => [
                    [
                        'type' => 'PACKAGE',
                        'length' => 20.0,
                        'width' => 15.0,
                        'height' => 10.0,
                        'weight' => 0.5,
                    ],
                ],
            ]),
        );

        $this->assertCount(1, $response->methods);
        $this->assertInstanceOf(Optional::class, $response->methods[0]->descriptions);
        $this->assertInstanceOf(Optional::class, $response->methods[0]->logo);
    }

    #[Test]
    public function it_wraps_agent_service_id_as_array_and_defaults_missing_locations(): void
    {
        $mockClient = new MockClient([
            GetAgentsRequest::class => MockResponse::make([
                'status' => 0,
                'error' => ['message' => 'The service id must be an array.'],
            ], 200),
        ]);

        $connector = ShipitConnector::basic('key', 'secret', ShipitConnector::TEST_BASE_URL);
        $connector->withMockClient($mockClient);

        $response = $connector->agents()->get([
            'serviceId' => 'posti.po2103',
            'country' => 'FI',
            'postcode' => '00100',
        ]);

        $this->assertSame(0, $response->status);
        $this->assertCount(0, $response->locations);

        $request = $mockClient->getLastPendingRequest();
        $this->assertNotNull($request);
        $this->assertSame(['posti.po2103'], $request->body()->all()['serviceId']);
    }

    #[Test]
    public function it_serializes_shipment_request_without_optional_fields(): void
    {
        $request = ShipmentRequestData::from([
            'sender' => $this->party(),
            'receiver' => $this->party(),
            'parcels' => [
                [
                    'length' => 20.0,
                    'width' => 15.0,
                    'height' => 10.0,
                    'weight' => 0.5,
                ],
            ],
            'serviceId' => 'posti.2103',
            'reference' => 'ORDER-1',
            'pickupId' => 'agent-1',
        ]);

        $payload = $request->toArray();

        $this->assertSame('posti.2103', $payload['serviceId']);
        $this->assertSame('ORDER-1', $payload['reference']);
        $this->assertSame('agent-1', $payload['pickupId']);
        $this->assertArrayNotHasKey('fragile', $payload);
        $this->assertInstanceOf(DataCollection::class, $request->parcels);
        $this->assertInstanceOf(ParcelData::class, $request->parcels[0]);
        $this->assertInstanceOf(PartyData::class, $request->sender);
    }

    /**
     * @return array<string, string>
     */
    private function party(): array
    {
        return [
            'name' => 'Acme Oy',
            'email' => 'sender@example.com',
            'phone' => '+358401000000',
            'address' => 'Example Street 1',
            'city' => 'Helsinki',
            'postcode' => '00100',
            'country' => 'FI',
        ];
    }
}
