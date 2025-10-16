# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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


[unreleased]: https://github.com/ajgarlag/openid-connect-provider-bundle/compare/0.2.1...HEAD
[0.2.1]: https://github.com/ajgarlag/openid-connect-provider-bundle/releases/tag/0.2.1
[0.2.0]: https://github.com/ajgarlag/openid-connect-provider-bundle/releases/tag/0.2.0
[0.1.0]: https://github.com/ajgarlag/openid-connect-provider-bundle/releases/tag/0.1.0
