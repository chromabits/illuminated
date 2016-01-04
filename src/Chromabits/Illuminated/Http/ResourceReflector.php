<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Http;

use Chromabits\Illuminated\Http\Entities\ResourceMethod;
use Chromabits\Illuminated\Http\Factories\ResourceFactory;
use Chromabits\Nucleus\Foundation\BaseObject;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use ReflectionClass;
use ReflectionParameter;

/**
 * Class ControllerReflector.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Foundation
 */
class ResourceReflector extends BaseObject
{
    /**
     * @var Container
     */
    protected $app;

    /**
     * Construct an instance of a ResourceReflector.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        parent::__construct();

        $this->app = $container;
    }

    /**
     * @param ResourceFactory $factory
     * @param ResourceMethod $method
     *
     * @return Request|null
     */
    public function getMethodRequest(
        ResourceFactory $factory,
        ResourceMethod $method
    ) {
        return $this->getRequest(
            $this->getMethodArgumentTypes($factory, $method)
        );
    }

    /**
     * @param ResourceFactory $factory
     * @param ResourceMethod $method
     *
     * @return ReflectionParameter[]
     */
    public function getMethodArgumentTypes(
        ResourceFactory $factory,
        ResourceMethod $method
    ) {
        $classReflector = new ReflectionClass($factory->getController());

        $methodReflector = $classReflector->getMethod($method->getMethod());

        return $methodReflector->getParameters();
    }

    /**
     * Get method route parameters.
     *
     * @param ResourceFactory $factory
     * @param ResourceMethod $method
     *
     * @return array
     */
    public function getMethodParameters(
        ResourceFactory $factory,
        ResourceMethod $method
    ) {
        $parameters = [];
        $types = $this->getMethodArgumentTypes($factory, $method);

        foreach ($types as $parameter) {
            if ($parameter->getClass() == null) {
                $parameters[] = $parameter->getName();
            }
        }

        return $parameters;
    }

    /**
     * @param ReflectionParameter[] $arguments
     *
     * @return Request|null
     */
    public function getRequest(array $arguments)
    {
        foreach ($arguments as $argument) {
            if ($argument->getClass() === null) {
                continue;
            }

            $type = $argument->getClass()->getName();

            if ($type === ApiCheckableRequest::class
                || in_array(ApiCheckableRequest::class, class_parents($type))
            ) {
                $this->app->bind('illuminated.skipCheckableRequest', true);

                $instance = $this->app->make($type);

                if ($instance instanceof ApiCheckableRequest) {
                    return $instance;
                }
            }
        }

        return null;
    }
}
