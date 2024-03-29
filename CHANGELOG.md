# Changelog

All notable changes to `swisnl/php-http-fixture-client` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## Unreleased

- Nothing

## [3.1.0] - 2024-02-23

### Changed
- Allow PSR 17 response factory in client
- Bumped `php-http/mock-client` to `^1.6`

### Fixed
- Allow installation of `psr/http-message: ^2.0`
- Allow installation of `symfony/string: ^7.0`

## [3.0.0] - 2022-01-10

### Changed
- The `ResponseBuilder` now requires PSR-17 factories instead of a PHP-HTTP factory, to align it with the `Client`. This is only a breaking change in the rare case where you provide your own factory to the `ResponseBuilder`.
- Removed `domainAliases` from constructor arguments. Please use `setDomainAliases()` on the instance instead.
- Replaced [danielstjules/stringy](https://packagist.org/packages/danielstjules/stringy) with [symfony/string](https://packagist.org/packages/symfony/string). In some edge cases, mainly special or accented characters, this will affect how query parameters are transformed to a fixture.

### Removed
- Dropped PHP <7.4 support.

## [2.4.0] - 2022-01-06

### Added
- Add an option to ignore certain query parameters. See readme for more info.

## [2.3.2] - 2021-08-30

### Fixed
- Fixed issue introduced by internal refactor

## [2.3.1] - 2021-08-30

### Fixed
- Fixed compatibility with `php-http/mock-client: ^1.5`

## [2.3.0] - 2021-07-12

### Added
- PHP 8 support

### Removed
- Dropped PHP <7.2 support

## [2.2.0] - 2019-03-06

### Added
- Add support for HTTPlug 2.0 and [PSR-18](https://www.php-fig.org/psr/psr-18/)

## [2.1.0] - 2018-10-05

### Added
- Added strict mode [#3](https://github.com/swisnl/php-http-fixture-client/pull/3)

## [2.0.0] - 2018-09-05

This release changes the way URIs with GET-params are resolved to fixtures. GET-params are now separated with `&` and key/value pairs are combined with `=` instead of `-`. Besides that, more characters are allowed in the filename. Please see the updated [README](https://github.com/swisnl/php-http-fixture-client/blob/master/README.md) and pull request [#2](https://github.com/swisnl/php-http-fixture-client/pull/2) for more information.

### Changed
- Changed logic for converting GET-params to a filename [#2](https://github.com/swisnl/php-http-fixture-client/pull/2)

## [1.0.0] - 2018-02-09

Initial release.
