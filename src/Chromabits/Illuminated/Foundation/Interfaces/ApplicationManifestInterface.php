<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Foundation\Interfaces;

use Chromabits\Illuminated\Http\Factories\ResourceFactory;
use Chromabits\Illuminated\Http\RouteAggregator;

/**
 * Interface ApplicationManifestInterface.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Foundation\Interfaces
 */
interface ApplicationManifestInterface
{
    /**
     * @return RouteAggregator
     */
    public function getRouteAggregator();

    /**
     * Get all the ResourceFactories for this application.
     *
     * @return ResourceFactory[]
     */
    public function getResources();

    /**
     * Get all the API ResourceFactories for this application.
     *
     * @return ResourceFactory[]
     */
    public function getApiResources();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return string
     */
    public function getCurrentVersion();

    /**
     * @return string[]
     */
    public function getProse();

    /**
     * @return string[]
     */
    public function getApiPrefixes();

    /**
     * @return string
     */
    public function getBaseUri();

    /**
     * @return array
     */
    public function getProperties();

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasProperty($key);

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getProperty($key);
}
