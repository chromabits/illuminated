<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Foundation\Modules;

use Chromabits\Illuminated\Conference\Module;
use Chromabits\Illuminated\Foundation\Controllers\ApplicationController;
use Chromabits\Illuminated\Foundation\Interfaces\ApplicationManifestInterface;
use Chromabits\Nucleus\Exceptions\CoreException;
use Chromabits\Nucleus\Http\Enums\HttpMethods;
use Illuminate\Contracts\Container\Container;

/**
 * Class ApplicationModule.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Foundation\Modules
 */
class ApplicationModule extends Module
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * Construct an instance of a ApplicationModule.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        parent::__construct();

        $this->container = $container;

        $this->register(
            'index',
            ApplicationController::class,
            'getIndex',
            'Summary'
        );

        $this->register(
            'prose',
            ApplicationController::class,
            'getProse',
            'Documentation'
        );

        $this->register(
            'single',
            ApplicationController::class,
            'getSingle',
            'Sngle Item',
            HttpMethods::GET,
            true
        );
    }

    /**
     * Boot the module.
     *
     * @throws CoreException
     */
    public function boot()
    {
        parent::boot();

        if (!$this->container->bound(ApplicationManifestInterface::class)) {
            throw new CoreException(
                'An instance of ApplicationManifest must be bound to in the ' .
                'container in order to provide application reflection data.'
            );
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'illuminated.conference.application';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Application';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Documentation based on reflection';
    }

    /**
     * Get the name of the default method.
     *
     * @return string|null
     */
    public function getDefaultMethodName()
    {
        return 'index';
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'fa-sitemap';
    }
}
