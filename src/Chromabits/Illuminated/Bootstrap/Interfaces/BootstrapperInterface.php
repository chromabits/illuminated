<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Bootstrap\Interfaces;

use Illuminate\Contracts\Foundation\Application;

/**
 * Interface BootstrapperInterface.
 *
 * A bootstrapper bootstraps a part of the application.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Bootstrap\Interfaces
 */
interface BootstrapperInterface
{
    /**
     * Bootstrap the given application.
     *
     * @param Application $app
     */
    public function bootstrap(Application $app);
}
