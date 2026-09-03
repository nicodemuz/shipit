# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed
- Made the SDK framework-agnostic: removed Laravel (`App` facade), Spatie Laravel Data, and the Saloon Laravel plugin.
- Replaced Spatie DTOs with a small `Cline\Shipit\Dto` layer that keeps `::from()` / `->toArray()`.
- Public `ShipitConnector` constructor for Symfony (and any other) DI container.
- Added `ShipitConnector::basic()` for Shipit.fi API key + secret (HTTP Basic).
- Composer package name is now `nicodemuz/shipit`.

