# Development

- [Logging](#logging)

## Logging

The plugin uses Monolog logging library for PHP.

The offical repository can be found on GitHub at https://github.com/Seldaek/monolog

Monolog is available on Packagist ([monolog/monolog](http://packagist.org/packages/monolog/monolog))
and as such installable via [Composer](http://getcomposer.org/).

Logging events is simple as. Get an instance of logger and log away.
```php
$logger = \enrol_selma\local\plugin_logger::get_logger();
$logger->critical('This is very very bad!');
```
