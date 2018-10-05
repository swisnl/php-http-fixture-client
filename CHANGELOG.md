# Changelog

All notable changes to `swisnl/php-http-fixture-client` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [2.1.0] - 2018-10-05

### Added
- Added strict mode [#3](https://github.com/swisnl/php-http-fixture-client/pull/3)

## [2.0.0] - 2018-09-05

This release changes the way URIs with GET-params are resolved to fixtures. GET-params are now separated with `&` and key/value pairs are combined with `=` instead of `-`. Besides that, more characters are allowed in the filename. Please see the updated [README](https://github.com/swisnl/php-http-fixture-client/blob/master/README.md) and pull request [#2](https://github.com/swisnl/php-http-fixture-client/pull/2) for more information.

### Changed
- Changed logic for converting GET-params to a filename [#2](https://github.com/swisnl/php-http-fixture-client/pull/2)

## [1.0.0] - 2018-02-09

Initial release.
