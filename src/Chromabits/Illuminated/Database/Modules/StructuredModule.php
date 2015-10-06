<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Database\Modules;

use Chromabits\Illuminated\Conference\Module;
use Chromabits\Illuminated\Database\Controllers\StructuredModuleController;
use Chromabits\Illuminated\Database\Interfaces\StructuredStatusInterface;
use Chromabits\Illuminated\Database\Migrations\Batch;
use Chromabits\Nucleus\Exceptions\CoreException;
use Illuminate\Contracts\Container\Container;

/**
 * Class StructuredModule.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Database\Modules
 */
class StructuredModule extends Module
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * Construct an instance of a StructuredModule.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        parent::__construct();

        $this->register(
            'index',
            StructuredModuleController::class,
            'getIndex',
            'Database Problems'
        );

        $this->register(
            'all',
            StructuredModuleController::class,
            'getAll',
            'All Migrations'
        );

        $this->container = $container;
    }

    /**
     * Boot the module.
     *
     * @throws CoreException
     */
    public function boot()
    {
        parent::boot();

        if (!$this->container->bound(StructuredStatusInterface::class)) {
            throw new CoreException(
                'This module requires the Structured service to be loaded. ' .
                'Please make sure that `StructuredMigrationsServiceProvider` ' .
                'is in your application\'s `config/app.php` file.'
            );
        }

        if (!$this->container->bound(Batch::class)) {
            throw new CoreException(
                'While the Structured service is loaded, there isn\'t a ' .
                'Batch defined at the moment. Please define a Batch class ' .
                'for your application and bind it using a service provider.'
            );
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'illuminated.database.structured';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Structured';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'View database migrations status.';
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
}
