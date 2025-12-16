# kaninstein/laravel-app-errors

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kaninstein/laravel-app-errors.svg?style=flat-square)](https://packagist.org/packages/kaninstein/laravel-app-errors)
[![Total Downloads](https://img.shields.io/packagist/dt/kaninstein/laravel-app-errors.svg?style=flat-square)](https://packagist.org/packages/kaninstein/laravel-app-errors)
[![License](https://img.shields.io/packagist/l/kaninstein/laravel-app-errors.svg?style=flat-square)](https://packagist.org/packages/kaninstein/laravel-app-errors)

Consistent JSON error contract for Laravel APIs with:

- Semantic HTTP statuses (422/401/403/404/409/412/503/500)
- `request_id` propagation via middleware (`X-Request-Id`)
- Environment-aware `debug` payloads

## Install

```bash
composer require kaninstein/laravel-app-errors
```

## Usage

Publish config:

```bash
php artisan vendor:publish --tag=app-errors-config
```

Add `Kaninstein\LaravelAppErrors\Http\Middleware\RequestIdMiddleware` to your API/web middleware stack and render JSON errors using `Kaninstein\LaravelAppErrors\Http\ExceptionMapper`.
