# Container

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]

Container is a lightweight dependency injection container.  It's compatible with the [PSR-11 container interface](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-11-container.md), so you can use it with lots of different projects out of the box.

The container is really simple; It's basically an array of identifier => callable mappings, and the callable is invoked to get the resulting object.

New to dependency injection and containers? I wrote a [blog post](http://mattallan.org/2016/dependency-injection-containers/) explaining dependency injection and how this container works.

This package is compliant with PSR-1, PSR-2 and PSR-4.

## Install

Via Composer

``` bash
$ composer require yuloh/container
```

## Usage

### Adding Entries

Adding an entry to the container is really simple.  Just specify the identifier as the first argument, and a callable as the second argument.

``` php
use Yuloh\Container\Container;

$container = new Container();

$container->set(Psr\Log\LoggerInterface::class, function () {
    $logger = new Monolog\Logger();
    $logger->pushHandler(new StreamHandler('error.log'));
    return $logger;
});
```

The closure will receive the container as it's only argument, so you can use the container to resolve the dependencies of your entry.

```php
$container->set('db', function ($container) {
    $db = new Database();
    $logger = $container->get(Psr\Log\LoggerInterface::class);
    $db->setLogger($logger);
    return $db;
});
```

All entries are shared (singletons), which means an entry will be resolved once and reused for subsequent calls.

### Getting Entries

To check if an entry exists, use `has`.  To get an entry, use `get`.  If you are just retrieving entries you can typehint `Psr\Container\ContainerInterface` instead of the actual Container.

```php
if ($container->has('db')) {
    $db = $container->get('db');
}
```
## Why Another Container?

There are **a lot** of containers out there.  I was working on a project and wanted a lightweight default container and couldn't find what I wanted.  This container:

- Implements container-interop.
- Supports PHP 5.4+
- Supports adding entries at runtime.
- Is incredibly lightweight, with the bare minimum of code to support the first 3 goals.

## Testing

``` bash
$ composer test
$ composer cs
```

[ico-version]: https://img.shields.io/packagist/v/yuloh/container.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/yuloh/container/master.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/yuloh/container
[link-travis]: https://travis-ci.org/yuloh/container
