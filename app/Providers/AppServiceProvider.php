<?php

namespace App\Providers;

use App\Auth\CustomPasswordBroker;
use App\Auth\CustomTokenRepository;
use Illuminate\Auth\Passwords\PasswordBrokerManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('auth.password', function ($app) {
            return new \App\Auth\CustomPasswordBrokerManager($app);
        });

        $this->app->bind('auth.password.broker', function ($app) {
            return $app->make('auth.password')->broker();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Our custom password broker implementation is now handled by CustomPasswordBrokerManager
    }
}
