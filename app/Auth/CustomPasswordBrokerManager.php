<?php

namespace App\Auth;

use Illuminate\Auth\Passwords\PasswordBrokerManager;

class CustomPasswordBrokerManager extends PasswordBrokerManager
{
    /**
     * Resolve the given broker.
     *
     * @param  string  $name
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new \InvalidArgumentException("Password resetter [{$name}] is not defined.");
        }

        // Create our custom token repository
        $tokens = new CustomTokenRepository(
            $this->app['db']->connection($config['connection'] ?? null),
            $this->app['hash'],
            $config['table'],
            $this->app['config']['app.key'],
            ($config['expire'] ?? 60) * 60,
            $config['throttle'] ?? 0
        );

        // Return our custom password broker with all required parameters
        return new CustomPasswordBroker(
            $tokens,
            $this->app['auth']->createUserProvider($config['provider'] ?? null),
            $this->app['events'] ?? null,
            null,
            $this->app['config']->get('auth.timebox_duration', 200000)
        );
    }
}
