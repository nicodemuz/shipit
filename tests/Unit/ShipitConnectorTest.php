<?php

declare(strict_types=1);

namespace Tests\Unit;

use Cline\Shipit\Auth\ShipitKeySecretAuthenticator;
use Cline\Shipit\Connector\ShipitConnector;
use Cline\Shipit\Data\ParcelData;
use Cline\Shipit\Data\PartyData;
use Cline\Shipit\Data\RegistrationRequestData;
use Cline\Shipit\Data\Responses\ShipmentResponseData;
use Cline\Shipit\Data\Responses\ShippingMethodListResponseData;
use Cline\Shipit\Data\Responses\TrackingEventResponseData;
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
    public function it_serializes_merchant_registration_request(): void
    {
        $request = RegistrationRequestData::from([
            'name' => 'Acme Oy',
            'email' => 'ops@acme.test',
            'phone' => '+358401000000',
            'address' => 'Street 1',
            'postcode' => '00100',
            'city' => 'Helsinki',
            'country' => 'FI',
            'isCompany' => true,
            'contactPerson' => 'Ada',
            'businessId' => '1234567-8',
            'subscribeNewsletter' => false,
        ]);

        $payload = $request->toArray();

        $this->assertSame('Acme Oy', $payload['name']);
        $this->assertSame('1234567-8', $payload['businessId']);
        $this->assertTrue($payload['isCompany']);
        $this->assertArrayNotHasKey('password', $payload);
    }

    #[Test]
    public function it_maps_registration_credentials_response(): void
    {
        $connector = ShipitConnector::basic('key', 'secret');
        $connector->withMockClient(new MockClient([
            MockResponse::make([
                'credentials' => [
                    'key' => 'new-key',
                    'secret' => 'new-secret',
                ],
            ], 200),
        ]));

        $response = $connector->user()->register(
            RegistrationRequestData::from([
                'name' => 'Acme Oy',
                'email' => 'ops@acme.test',
                'phone' => '+358401000000',
                'address' => 'Street 1',
                'postcode' => '00100',
                'city' => 'Helsinki',
                'country' => 'FI',
            ]),
        );

        $this->assertTrue($response->hasCredentials());
        $this->assertSame('new-key', $response->credentials->key);
        $this->assertSame('new-secret', $response->credentials->secret);
    }

    #[Test]
    public function it_includes_shipping_method_filters_in_request_body(): void
    {
        $request = ShippingMethodsRequestData::from([
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
            'serviceId' => ['posti.po2103', 'mh.mh80'],
            'retrievePickupLocations' => true,
            'maxPickupLocations' => 10,
            'fragile' => false,
            'dangerous' => false,
            'companyIsSending' => true,
            'companyIsReceiving' => false,
        ]);

        $payload = $request->toArray();

        $this->assertSame(['posti.po2103', 'mh.mh80'], $payload['serviceId']);
        $this->assertTrue($payload['retrievePickupLocations']);
        $this->assertSame(10, $payload['maxPickupLocations']);
    }

    #[Test]
    public function it_maps_shipment_return_tracking_and_success_status(): void
    {
        $response = ShipmentResponseData::from([
            'status' => 1,
            'trackingNumber' => 'JJFI1',
            'trackingUrls' => ['https://track.example/1'],
            'orderId' => '99',
            'freightDoc' => ['https://docs.example/1.pdf'],
            'returnTrackingNumber' => 'RET1',
            'returnTrackingUrls' => ['https://track.example/ret'],
        ]);

        $this->assertTrue($response->isSuccess());
        $this->assertSame('RET1', $response->returnTrackingNumber);
        $this->assertSame(['https://track.example/ret'], $response->returnTrackingUrls);
    }

    #[Test]
    public function it_maps_query_tracking_events_with_happened_at(): void
    {
        $response = TrackingEventResponseData::from([
            'data' => [
                [
                    'status' => 'DELIVERED',
                    'happened_at' => '2026-08-13T14:33:00+03:00',
                    'description' => ['fi' => 'Toimitettu', 'en' => 'Delivered'],
                    'information' => ['fi' => 'Lisätieto'],
                ],
            ],
        ]);

        $this->assertCount(1, $response->data);
        $this->assertSame('DELIVERED', $response->data[0]->status);
        $this->assertSame('2026-08-13T14:33:00+03:00', $response->data[0]->happenedAt);
        $this->assertSame('Toimitettu', $response->data[0]->description['fi']);
    }

    #[Test]
    public function it_parses_list_methods_catalog_entries(): void
    {
        $response = ShippingMethodListResponseData::from([
            [
                'serviceId' => 'mh.mh80',
                'name' => 'Lähellä-paketti',
                'carrier' => 'Matkahuolto',
                'domesticDeliveries' => true,
                'homeDelivery' => false,
                'pickUpPoints' => true,
                'logo' => 'https://example.com/logo.png',
            ],
            [
                // Incomplete row is skipped
                'serviceId' => 'incomplete',
                'carrier' => 'X',
            ],
        ]);

        $this->assertCount(1, $response->data);
        $this->assertSame('mh.mh80', $response->data[0]->serviceId);
        $this->assertTrue($response->data[0]->pickUpPoints);
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
                    'contents' => 'Widgets',
                ],
            ],
            'serviceId' => 'posti.2103',
            'reference' => 'ORDER-1',
            'pickupId' => 'agent-1',
            'companyIsSending' => true,
            'companyIsReceiving' => false,
            'isTestShipment' => false,
            'pickup' => true,
        ]);

        $payload = $request->toArray();

        $this->assertSame('posti.2103', $payload['serviceId']);
        $this->assertSame('ORDER-1', $payload['reference']);
        $this->assertSame('agent-1', $payload['pickupId']);
        $this->assertTrue($payload['companyIsSending']);
        $this->assertFalse($payload['companyIsReceiving']);
        $this->assertFalse($payload['isTestShipment']);
        $this->assertTrue($payload['pickup']);
        $this->assertSame('Widgets', $payload['parcels'][0]['contents']);
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
