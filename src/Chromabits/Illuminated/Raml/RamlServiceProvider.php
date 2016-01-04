<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Raml;

use Chromabits\Illuminated\Raml\Interfaces\RamlEncoderInterface;
use Chromabits\Illuminated\Support\ServiceMapProvider;

/**
 * Class RamlServiceProvider.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Raml
 */
class RamlServiceProvider extends ServiceMapProvider
{
    /**
     * @var bool
     */
    protected $defer = true;

    /**
     * @var array
     */
    protected $map = [
        RamlEncoderInterface::class => RamlEncoder::class,
    ];
}
