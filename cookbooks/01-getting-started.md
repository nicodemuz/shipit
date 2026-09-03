# Getting Started

## Installation

```bash
composer config repositories.nicodemuz-shipit vcs https://github.com/nicodemuz/shipit
composer require nicodemuz/shipit:dev-main
```

## Requirements

- PHP 8.4+
- Saloon v3

## Basic Setup

```php
use Cline\Shipit\Auth\ShipitKeySecretAuthenticator;
use Cline\Shipit\Connector\ShipitConnector;

// Shipit.fi merchant credentials (X-SHIPIT-KEY + X-SHIPIT-SECRET)
$shipit = ShipitConnector::basic('your-api-key', 'your-api-secret');

// Bearer token
$shipit = ShipitConnector::token('your-api-token');

// Explicit constructor (Symfony DI)
$shipit = new ShipitConnector(
    ShipitConnector::TEST_BASE_URL,
    new ShipitKeySecretAuthenticator('your-api-key', 'your-api-secret'),
);

// Test API
$shipit = ShipitConnector::basic(
    'your-api-key',
    'your-api-secret',
    ShipitConnector::TEST_BASE_URL,
);
```

## Symfony container

```yaml
services:
    Cline\Shipit\Auth\ShipitKeySecretAuthenticator:
        arguments:
            $apiKey: '%env(SHIPIT_API_KEY)%'
            $apiSecret: '%env(SHIPIT_API_SECRET)%'

    Cline\Shipit\Connector\ShipitConnector:
        arguments:
            $baseUrl: '%env(SHIPIT_API_BASE_URL)%'
            $auth: '@Cline\Shipit\Auth\ShipitKeySecretAuthenticator'
```

## Available Resources

Once you have a connector instance, you can access all resources:

```php
$shipit->shippingMethods()      // Query shipping methods and rates
$shipit->shipments()            // Create and manage shipments
$shipit->agents()               // Find pickup/delivery locations
$shipit->postalCodes()          // Validate postal codes
$shipit->locations()            // Manage sender/receiver addresses
$shipit->organizations()        // Manage organizations
$shipit->tracking()             // Track shipments
$shipit->user()                 // Manage user account
$shipit->balance()              // Check balance and transactions
$shipit->carrierContracts()     // Manage carrier contracts
$shipit->consignmentTemplates() // Manage shipment templates
$shipit->organizationMembers()  // Manage team members
```

## Response Handling

All methods return typed DTOs that throw exceptions on HTTP errors:

```php
try {
    $methods = $shipit->shippingMethods()->get($requestData);

    foreach ($methods->methods as $method) {
        echo $method->serviceName;
        echo $method->price;
    }
} catch (\Saloon\Exceptions\Request\FatalRequestException $e) {
    $response = $e->getResponse();
    $statusCode = $response->status();
    $errorData = $response->json();
}
```

## Next Steps

- [Shipping Methods](./02-shipping-methods.md) - Query available carriers and rates
- [Creating Shipments](./03-creating-shipments.md) - Create shipments and labels
- [Service Points](./04-service-points.md) - Find pickup and delivery locations
- [Postal Codes](./05-postal-codes.md) - Validate addresses
