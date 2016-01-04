<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Foundation;

use Chromabits\Illuminated\Foundation\Interfaces\ApplicationManifestInterface;
use Chromabits\Illuminated\Http\Factories\ResourceFactory;
use Chromabits\Illuminated\Http\ResourceAggregator;
use Chromabits\Illuminated\Http\RouteAggregator;
use Chromabits\Nucleus\Foundation\BaseObject;
use Chromabits\Nucleus\Support\Arr;
use Chromabits\Nucleus\Support\Std;
use Chromabits\Nucleus\Support\Str;
use Illuminate\Contracts\Routing\UrlGenerator;

/**
 * Class ApplicationManifest.
 *
 * Declares information about a web application.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Foundation
 */
abstract class ApplicationManifest extends BaseObject implements
    ApplicationManifestInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $currentVersion;

    /**
     * @var UrlGenerator
     */
    protected $urlGenerator;

    /**
     * Construct an instance of a ApplicationManifest.
     *
     * @param UrlGenerator $urlGenerator
     */
    public function __construct(UrlGenerator $urlGenerator)
    {
        parent::__construct();

        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @return RouteAggregator
     */
    abstract public function getRouteAggregator();

    /**
     * Get all the ResourceFactories for this application.
     *
     * @return ResourceFactory[]
     */
    public function getResources()
    {
        return Std::reduce(
            function (array $resources, $mapper) {
                if ($mapper instanceof ResourceFactory) {
                    return array_merge($resources, [$mapper]);
                } elseif ($mapper instanceof ResourceAggregator) {
                    return array_merge($resources, $mapper->getResources());
                }

                return $resources;
            },
            [],
            $this->getRouteAggregator()->getMapperInstances()
        );
    }

    /**
     * Get all the API ResourceFactories for this application.
     *
     * @return ResourceFactory[]
     */
    public function getApiResources()
    {
        return Std::filter(function (ResourceFactory $resource) {
            foreach ($this->getApiPrefixes() as $prefix) {
                if (Str::beginsWith($resource->getPrefix(), $prefix)) {
                    return true;
                }
            }

            return false;
        }, $this->getResources());
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getCurrentVersion()
    {
        return $this->currentVersion;
    }

    /**
     * @return string[]
     */
    public function getProse()
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getApiPrefixes()
    {
        return [];
    }

    /**
     * @return string
     */
    public function getBaseUri()
    {
        return $this->urlGenerator->to('/');
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return [];
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasProperty($key)
    {
        return Arr::has($this->getProperties(), $key);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getProperty($key)
    {
        return Arr::dotGet($this->getProperties(), $key);
    }
}
