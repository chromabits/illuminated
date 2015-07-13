<?php

/**
 * Copyright 2015, Eduardo Trujillo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Laravel Helpers package
 */

namespace Tests\Chromabits\Illuminated\Alerts;

use Chromabits\Illuminated\Alerts\AlertServiceProvider;
use Tests\Chromabits\Support\HelpersTestCase;

/**
 * Class AlertServiceProviderTestInternal
 *
 * @package Tests\Chromabits\Illuminated\Alerts
 */
class AlertServiceProviderTestInternal extends HelpersTestCase
{
    public function testRegister()
    {
        $provider = new AlertServiceProvider($this->app);

        $provider->register();

        $this->assertTrue($this->app->bound('Chromabits\Illuminated\Contracts\Alerts\AlertManager'));
    }

    public function testProvides()
    {
        $provider = new AlertServiceProvider($this->app);

        $this->assertInternalType('array', $provider->provides());
    }
}
