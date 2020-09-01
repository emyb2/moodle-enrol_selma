# Development

- [Logging](#logging)
- [Guzzle](#guzzle)
- [PHPUnit](#phpunit)
- [Grunt](#grunt)
- [TravisCI](#travisci)
- [GitLabCI](#gitlabci)

## Logging

The plugin uses Monolog logging library for PHP.

The official repository can be found on GitHub at https://github.com/Seldaek/monolog

Monolog is available on Packagist ([monolog/monolog](http://packagist.org/packages/monolog/monolog))
and as such installable via [Composer](http://getcomposer.org/).

Logging events is simple as. Get an instance of logger and log away.
```php
$logger = \enrol_selma\local\plugin_logger::get_logger();
$logger->critical('This is very very bad!');
```
___
## Guzzle

The plugin uses Guzzle to send HTTP requests and receive HTTP responses.

Guzzle's code repository is available at https://github.com/guzzle/guzzle

Guzzle is available at Packagist [(guzzlehttp/guzzle](https://packagist.org/packages/guzzlehttp/guzzle))
and installable via [Composer](http://getcomposer.org/).

**Usage:**
```php
// Based on Guzzle docs.
// Create a client with a base URI
$client = new GuzzleHttp\Client(['base_uri' => 'https://foo.com/api/']);

// Send request with parameters.
$response = $client->request('GET', 'http://httpbin.org?foo=bar');

// Get a header from the response.
echo $response->getHeader('Content-Length')[0];
```

[Read more...](https://readthedocs.org/projects/guzzle/).
___
## PHPUnit
___
## Grunt
___
## TravisCI
___
## GitLabCI
GitLab is also used and makes testing the plugin locally easier.