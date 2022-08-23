# Fixture client for PHP-HTTP

[![PHP from Packagist](https://img.shields.io/packagist/php-v/swisnl/php-http-fixture-client.svg)](https://packagist.org/packages/swisnl/php-http-fixture-client)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/swisnl/php-http-fixture-client.svg)](https://packagist.org/packages/swisnl/php-http-fixture-client)
[![Software License](https://img.shields.io/packagist/l/swisnl/php-http-fixture-client.svg)](https://github.com/swisnl/php-http-fixture-client/blob/master/LICENSE)
[![Buy us a tree](https://img.shields.io/badge/Treeware-%F0%9F%8C%B3-lightgreen.svg)](https://plant.treeware.earth/swisnl/php-http-fixture-client)
[![Build Status](https://img.shields.io/github/checks-status/swisnl/php-http-fixture-client/master?label=tests)](https://github.com/swisnl/php-http-fixture-client/actions/workflows/tests.yml)
[![Scrutinizer Coverage](https://img.shields.io/scrutinizer/coverage/g/swisnl/php-http-fixture-client.svg)](https://scrutinizer-ci.com/g/swisnl/php-http-fixture-client/?branch=master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/swisnl/php-http-fixture-client.svg)](https://scrutinizer-ci.com/g/swisnl/php-http-fixture-client/?branch=master)
[![Made by SWIS](https://img.shields.io/badge/%F0%9F%9A%80-made%20by%20SWIS-%230737A9.svg)](https://www.swis.nl)

This is a fixture client for PHP-HTTP and is meant for testing purposes.
It maps requests to static fixtures.

## Install

``` bash
$ composer require --dev swisnl/php-http-fixture-client
```

## Usage

``` php
// Create client
$responseBuilder = new \Swis\Http\Fixture\ResponseBuilder('/path/to/fixtures');
$client = new \Swis\Http\Fixture\Client($responseBuilder);

// Send request
$response = $client->sendRequest(new Request(...));
```

## Fixture mapping

All requests send using this client are mapped to static fixtures located in the provided path.
URLs are transformed to file paths by using the domain and path fragments and (optionally) the method and/or the query params (sorted alphabetically).
A list of possible fixture paths is made and handled in order of specificity:

 1. {path}.{query}.{method}.mock
 2. {path}.{query}.mock
 3. {path}.{method}.mock
 4. {path}.mock

Please see the following table for some examples.

| URL | Method | Possible fixtures (in order of specificity) |
| --- | ------ | ------------------------------------------- |
| http://example.com/api/articles/1 | GET | /path/to/fixtures/example.com/api/articles/1.get.mock |
|                                   |     | /path/to/fixtures/example.com/api/articles/1.mock |
| http://example.com/api/articles | POST | /path/to/fixtures/example.com/api/articles.post.mock |
|                                 |      | /path/to/fixtures/example.com/api/articles.mock |
| http://example.com/api/comments?query=json | GET | /path/to/fixtures/example.com/api/comments.query=json.get.mock |
|                                            |     | /path/to/fixtures/example.com/api/comments.query=json.mock |
|                                            |     | /path/to/fixtures/example.com/api/comments.get.mock |
|                                            |     | /path/to/fixtures/example.com/api/comments.mock |
| http://example.com/api/comments?query=json&order=id | GET | /path/to/fixtures/example.com/api/comments.order=id&query=json.get.mock |
|                                                     |     | /path/to/fixtures/example.com/api/comments.order=id&query=json.mock |
|                                                     |     | /path/to/fixtures/example.com/api/comments.get.mock |
|                                                     |     | /path/to/fixtures/example.com/api/comments.mock |

### Domain aliases
The `ReponseBuilder` can be instructed to use aliases for domains using `setDomainAliases([...])`.
When configured, the provided aliases will be normalized when transforming requests to file paths.
You should provide aliases in the form of `['alias' => 'abstract']`.

### Ignored query parameters
The `ReponseBuilder` can be instructed to ignore certain query parameters using `setIgnoredQueryParameters([...])`.
When configured, the provided parameters will be ignored when transforming requests to file paths.
You should only provide the parameter name, not the value.
This allows you to ignore 'dynamic' parameters that change in each test execution.
Parameters are matched strictly, after url decoding, so 'foo' will match 'foo=bar', but not 'foo[]=bar'.

### Strict mode
The `ReponseBuilder` can be set to strict mode using `setStrictMode(true)`.
When in strict mode, only the first possible fixture path will be used.
This means that both the method and query params must be present in the fixture file name and it does not fall back to other fixture files.

### Helper
<UrlHelper>Please see <a href="https://swisnl.github.io/php-http-fixture-client/#helper">https://swisnl.github.io/php-http-fixture-client/#helper</a> for the URL helper.</UrlHelper>

### Body

The body of a request is loaded directly from a fixture with the file extension _.mock_.
The contents of this file can be anything that is a valid HTTP response, e.g. HTML, JSON or even images.
If a fixture can not be found, a `MockNotFoundException` will be thrown.
This exception has a convenience method `getPossiblePaths()` which lists all file paths that were checked, in order of specificity.

### Headers (optional)

The headers of a request are loaded from a fixture with the file extension _.headers_.
This is a simple JSON object with headers, e.g.
``` json
{
  "X-Made-With": "PHPUnit"
}
```

### Status (optional)

The status code of a request is loaded from a fixture with the file extension _.status_.
This is a plain file containing only the [HTTP status code](https://httpstatuses.com/).
If no _.status_ file is found, _200 OK_ will be used.

## Mocks (advanced)

This client extends [php-http/mock-client](https://github.com/php-http/mock-client), which allows you to add custom responses and exceptions that ignore fixture files. Please see the [mock-client documentation](https://github.com/php-http/mock-client#documentation) for more information.

N.B. A default response can not be set because this client uses that under the hood.

## Change log

Please see [CHANGELOG](https://github.com/swisnl/php-http-fixture-client/blob/master/CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](https://github.com/swisnl/php-http-fixture-client/blob/master/CONTRIBUTING.md) and [CODE_OF_CONDUCT](https://github.com/swisnl/php-http-fixture-client/blob/master/CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email security@swis.nl instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](https://github.com/swisnl/php-http-fixture-client/blob/master/LICENSE.md) for more information.

This package is [Treeware](https://treeware.earth). If you use it in production, then we ask that you [**buy the world a tree**](https://plant.treeware.earth/swisnl/php-http-fixture-client) to thank us for our work. By contributing to the Treeware forest you’ll be creating employment for local families and restoring wildlife habitats.

## SWIS :heart: Open Source

[SWIS](https://www.swis.nl) is a web agency from Leiden, the Netherlands. We love working with open source software.
