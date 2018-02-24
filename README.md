# Aura Filter Middleware

[![Build Status](https://secure.travis-ci.org/knoxzin1/aura-filter-middleware.svg?branch=master)](https://secure.travis-ci.org/knoxzin1/aura-filter-middleware)
[![Coverage Status](https://coveralls.io/repos/github/knoxzin1/aura-filter-middleware/badge.svg?branch=master)](https://coveralls.io/github/knoxzin1/aura-filter-middleware?branch=master)

Middleware to filter [Zend Expressive](https://github.com/zendframework/zend-expressive) requests using [Aura.Filter](https://github.com/auraphp/Aura.Filter).

## Requirements

* PHP >= 7.1

## Installation

This package is installable and autoloadable via Composer as [knoxzin1/aura-filter-middleware](https://packagist.org/packages/knoxzin1/aura-filter-middleware).

```sh
composer require knoxzin1/aura-filter-middleware
```
## Example

Add the middleware to your pipeline

```php
$app->pipe(AuraFilterMiddleware::class);
```

Pass the desired filter to the router options

```php
[
    'name' => 'foo',
    'path' => '/fooo',
    'middleware' => FooMiddleware::class,
    'allowed_methods' => ['POST'],
    'options' => [
        'aura-filter' => FooFilter::class,
    ],
],
```

The resulting object will be avaiable with the `validationResult` attribute name.
