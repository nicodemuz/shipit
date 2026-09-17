# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.2.0] - 2026-09-17

### Added
- Merchant registration DTOs aligned with Shipit.fi (`credentials.key` / `credentials.secret`).
- Shipping-methods request filters: `serviceId`, `retrievePickupLocations`, `maxPickupLocations`.
- Shipment response return tracking fields and `isSuccess()` (`status === 1`).
- Typed tracking-event query response (`data[]` with `happened_at` mapping).
- Listed shipping-method catalog DTO for `/v1/list-methods`.

### Changed
- Service-point location fields match shipping-methods payload (`address1`, `zipcode`, `countryCode`, …).
- Shipping method quotes include `delivery` / `deliveryTimezone`.
- Shipment request supports `isTestShipment`, `companyIsSending`, `companyIsReceiving`, `pickup`.
- Parcel request supports `contents`.

## [1.1.0] - 2026-09-03

### Changed
- Require `saloonphp/saloon` `^4.0` (clears known Saloon 3.x security advisories).

## [1.0.0] - 2026-09-03

### Changed
- Made the SDK framework-agnostic: removed Laravel (`App` facade), Spatie Laravel Data, and the Saloon Laravel plugin.
- Replaced Spatie DTOs with a small `Cline\Shipit\Dto` layer that keeps `::from()` / `->toArray()`.
- Public `ShipitConnector` constructor for Symfony (and any other) DI container.
- Composer package name is now `nicodemuz/shipit`.

### Fixed
- Authenticate with `X-SHIPIT-KEY` / `X-SHIPIT-SECRET` via `ShipitKeySecretAuthenticator` (HTTP Basic is rejected by Shipit.fi).
- Normalize `GetAgentsRequest` `serviceId` to an array and default missing `locations` to `[]` for the test API.
- Treat null Optional DTO fields as `Optional::create()`.
