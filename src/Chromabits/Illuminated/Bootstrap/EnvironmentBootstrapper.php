<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Bootstrap;

use Chromabits\Illuminated\Bootstrap\Interfaces\BootstrapperInterface;
use Dotenv;
use Illuminate\Contracts\Foundation\Application;
use InvalidArgumentException;

/**
 * Class EnvironmentBootstrapper.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Bootstrap
 */
class EnvironmentBootstrapper implements BootstrapperInterface
{
    const DEFAULT_ENVIRONMENT = 'stasis';

    /**
     * Bootstrap the given application.
     *
     * @param Application $app
     */
    public function bootstrap(Application $app)
    {
        // Attempt to load the default .env
        try {
            Dotenv::load($app->environmentPath(), $app->environmentFile());
        } catch (InvalidArgumentException $e) {
            //
        }

        // Attempt to load the environment-specific .env
        try {
            Dotenv::load($app->environmentPath(), vsprintf('.%s.env', [
                env('APP_ENV', static::DEFAULT_ENVIRONMENT),
            ]));
        } catch (InvalidArgumentException $e) {
            //
        }

        $app->detectEnvironment(function () {
            return env('APP_ENV', static::DEFAULT_ENVIRONMENT);
        });
    }
}
