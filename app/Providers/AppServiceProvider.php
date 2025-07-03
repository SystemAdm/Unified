<?php

namespace App\Providers;

use App\Auth\CustomPasswordBroker;
use App\Auth\CustomTokenRepository;
use Illuminate\Auth\Passwords\PasswordBrokerManager;
use Illuminate\Support\ServiceProvider;
use libphonenumber\PhoneNumberUtil;

class AppServiceProvider extends ServiceProvider
{
    private PhoneNumberUtil $phoneUtil;
    private string $countryCode;

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

        // Binder PhoneNumberUtil til tjenestebeholderen som en singleton
        $this->app->singleton(PhoneNumberUtil::class, function () {
            return PhoneNumberUtil::getInstance();
        });

        // Binder country code til tjenestebeholderen
        $this->app->singleton('countryCode', function () {
            return 'NO'; // Norge
        });

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}
