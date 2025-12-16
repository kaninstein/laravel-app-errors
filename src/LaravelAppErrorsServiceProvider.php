<?php

namespace Kaninstein\LaravelAppErrors;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Kaninstein\LaravelAppErrors\Http\ExceptionMapper;

final class LaravelAppErrorsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/app-errors.php', 'app-errors');

        $this->app->singleton(ExceptionMapper::class, function (Application $app) {
            return new ExceptionMapper((string) config('app-errors.default_public_message', 'Request failed.'));
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/app-errors.php' => config_path('app-errors.php'),
        ], 'app-errors-config');
    }
}

