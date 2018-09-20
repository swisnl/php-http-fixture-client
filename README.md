# Fixture client for PHP-HTTP

[![PHP from Packagist](https://img.shields.io/packagist/php-v/swisnl/php-http-fixture-client.svg)](https://packagist.org/packages/swisnl/php-http-fixture-client)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/swisnl/php-http-fixture-client.svg)](https://packagist.org/packages/swisnl/php-http-fixture-client)
[![Software License](https://img.shields.io/packagist/l/swisnl/php-http-fixture-client.svg)](https://github.com/swisnl/php-http-fixture-client/LICENSE) 
[![Run Status](https://api.shippable.com/projects/5a7d7deb260fde0600abe59e/badge?branch=master)](https://app.shippable.com/github/swisnl/php-http-fixture-client)
[![Coverage Badge](https://api.shippable.com/projects/5a7d7deb260fde0600abe59e/coverageBadge?branch=master)](https://app.shippable.com/github/swisnl/php-http-fixture-client)
[![Made by SWIS](https://img.shields.io/badge/%F0%9F%9A%80-made%20by%20SWIS-%23D9021B.svg)](https://www.swis.nl)

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

## Change log

Please see [CHANGELOG](https://github.com/swisnl/php-http-fixture-client/CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](https://github.com/swisnl/php-http-fixture-client/CONTRIBUTING.md) and [CODE_OF_CONDUCT](https://github.com/swisnl/php-http-fixture-client/CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email security@swis.nl instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](https://github.com/swisnl/php-http-fixture-client/LICENSE.md) for more information.

## SWIS

[SWIS](https://www.swis.nl) is a web agency from Leiden, the Netherlands. We love working with open source software. 
