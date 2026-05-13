# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Add a new `$clientClass` parameter to `Driver` to support custom clients
- Add `kid` value in JWKS
- Add `userinfo_endpoint_route` option to OpenID Connect Discovery configuration
- Add a new optional `$userinfoEndpointRoute` parameter to `DiscoveryController` to support custom userinfo endpoint route

### Changed

- Change the position of the `$tablePrefix` parameter in the `Driver` class

## [0.2.5] - 2026-04-03

### Fixed

- Fix `IdToken::getClaim` method to return default value if claim is not found
- Fix `nonce` handling in `AuthCodeGrant` to only set if present in request
- Fix error messages in `EndSessionController` for ID token verification

## [0.2.4] - 2026-02-19

### Removed

- Remove support for Symfony 7.2
- Remove support for Symfony 7.3

## [0.2.3] - 2026-02-19

### Added

- Add support for PHP 8.5
- Add support for Symfony 7.4
- Add support for Symfony 8.0

### Removed

- Remove support for PHP 8.1

## [0.2.2] - 2025-11-05

### Fixed

- Avoid trying to issue an `id_token` with client credentials grant

## [0.2.1] - 2025-10-16

### Added

- Add `SessionSidTrait::getSid` method to retrieve the session ID if it exists without generating a new one

### Fixed

- Fix `sid` comparison while ending session

## [0.2.0] - 2025-10-13

### Added

- RP-Inititated Logout support

### Changed

- Rename `ClaimsResolveEvent` to `UserClaimsResolveEvent`

## [0.1.0] - 2025-07-07

### Added

- Authentication using the Authorization Code Flow
- Authentication using the Implicit Flow
- OpenID Connect Discovery


[unreleased]: https://github.com/ajgarlag/openid-connect-provider-bundle/compare/0.2.5...HEAD
[0.2.5]: https://github.com/ajgarlag/openid-connect-provider-bundle/releases/tag/0.2.5
[0.2.4]: https://github.com/ajgarlag/openid-connect-provider-bundle/releases/tag/0.2.4
[0.2.3]: https://github.com/ajgarlag/openid-connect-provider-bundle/releases/tag/0.2.3
[0.2.2]: https://github.com/ajgarlag/openid-connect-provider-bundle/releases/tag/0.2.2
[0.2.1]: https://github.com/ajgarlag/openid-connect-provider-bundle/releases/tag/0.2.1
[0.2.0]: https://github.com/ajgarlag/openid-connect-provider-bundle/releases/tag/0.2.0
[0.1.0]: https://github.com/ajgarlag/openid-connect-provider-bundle/releases/tag/0.1.0
