# Container

Container is a really simple, minimalist dependency injection container.  It's compatible with [container-interop](https://github.com/container-interop/container-interop), so you can use it with lots of different projects out of the box.

The code is really easy to read; It's basically an array of identifier => callable mappings, and the callable is invoked to get the resulting object.

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

### Getting Entries

To check if an entry exists, use `has`.  To get an entry, use `get`.  If you are just retrieving entries you can typehint `Interop\Container\ContainerInterface` instead of the actual Container.

```php
if ($container->has('db')) {
    $db = $container->get('db');
}
```

### Array Usage

The container implements [ArrayAccess](http://php.net/manual/en/class.arrayaccess.php), so you can use the container as if it was an array.  It's a little less verbose.

```php
$container['templates'] = function () {
    return new League\Plates\Engine('/resources/views');
};
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
