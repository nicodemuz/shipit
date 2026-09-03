# Shipit SDK

A **framework-agnostic** [Saloon](https://docs.saloon.dev) PHP SDK for the [Shipit.fi](https://www.shipit.fi) API.

This is a fork of [`cline/shipit`](https://github.com/faustbrian/shipit) with Laravel removed: no `App` facade, no Spatie Laravel Data, no Saloon Laravel plugin. It works with **Symfony**, Laravel, or plain PHP.

## Requirements

> **Requires [PHP 8.4+](https://php.net/releases/)**

## Installation

Until the package is on Packagist under this fork, require a tagged release from GitHub:

```bash
composer config repositories.nicodemuz-shipit vcs https://github.com/nicodemuz/shipit
composer require nicodemuz/shipit:^1.0
```

## Symfony

Register the Saloon connector in the container. Shipit.fi merchant credentials use the `X-SHIPIT-KEY` and `X-SHIPIT-SECRET` headers (`ShipitKeySecretAuthenticator`). Use `https://apitest.shipit.ax` for non-production.

```yaml
# config/services.yaml
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

Then inject `ShipitConnector` as usual.

## Quick Start

```php
use Cline\Shipit\Auth\ShipitKeySecretAuthenticator;
use Cline\Shipit\Connector\ShipitConnector;

// Shipit.fi merchant API (key + secret headers)
$shipit = ShipitConnector::basic('your-api-key', 'your-api-secret');

// Or Bearer token
$shipit = ShipitConnector::token('your-api-token');

// Explicit base URL (Symfony / custom environments)
$shipit = new ShipitConnector(
    ShipitConnector::TEST_BASE_URL,
    new ShipitKeySecretAuthenticator('your-api-key', 'your-api-secret'),
);

// Test environment
$shipit = ShipitConnector::basic(
    'your-api-key',
    'your-api-secret',
    ShipitConnector::TEST_BASE_URL,
);
```

DTOs still use `::from()` / `->toArray()` (Spatie-compatible API, no Laravel):

```php
use Cline\Shipit\Data\ShippingMethodsRequestData;

$methods = $shipit->shippingMethods()->get(
    ShippingMethodsRequestData::from([
        'sender' => [/* name, email, phone, address, city, postcode, country */],
        'receiver' => [/* ... */],
        'parcels' => [
            ['length' => 30, 'width' => 20, 'height' => 10, 'weight' => 2.5],
        ],
    ]),
);
```

## Documentation

Cookbooks (API usage is unchanged):

- [Getting Started](cookbooks/01-getting-started.md)
- [Shipping Methods](cookbooks/02-shipping-methods.md)
- [Creating Shipments](cookbooks/03-creating-shipments.md)
- [Service Points](cookbooks/04-service-points.md)
- [Postal Codes](cookbooks/05-postal-codes.md)
- [Tracking](cookbooks/06-tracking.md)
- [Locations](cookbooks/07-locations.md)
- [Organizations](cookbooks/08-organizations.md)
- [User Management](cookbooks/09-user-management.md)
- [Balance & Accounting](cookbooks/10-balance-accounting.md)
- [Advanced Features](cookbooks/11-advanced-features.md)
- [Error Handling](cookbooks/12-error-handling.md)
- [Testing](cookbooks/13-testing.md)

## Features

- **Saloon v3** HTTP client
- **Typed DTOs** without Laravel
- Public constructor — bind in any DI container
- Basic auth for Shipit.fi API key + secret
- Live (`https://api.shipit.fi`) and test (`https://apitest.shipit.ax`) endpoints

## License

The MIT License. Please see [License File](LICENSE.md) for more information.
