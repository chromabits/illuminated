<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Http\Factories;

use Chromabits\Illuminated\Http\Entities\ResourceMethod;
use Chromabits\Nucleus\Exceptions\LackOfCoffeeException;
use Chromabits\Nucleus\Foundation\BaseObject;
use Chromabits\Nucleus\Http\Enums\HttpMethods;
use Chromabits\Nucleus\Meditation\Arguments;
use Chromabits\Nucleus\Meditation\Boa;
use Chromabits\Nucleus\Meditation\Exceptions\InvalidArgumentException;
use Chromabits\Nucleus\Support\Arr;
use Chromabits\Nucleus\Support\Std;
use Illuminate\Routing\Router;

/**
 * Class ResourceFactory.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Http\Factories
 */
class ResourceFactory extends BaseObject
{
    /**
     * @var string
     */
    protected $controller;

    /**
     * @var array
     */
    protected $middleware;

    /**
     * @var array
     */
    protected $methods;

    /**
     * @var string|null
     */
    protected $prefix;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * Construct an instance of a ResourceFactory.
     *
     * @param string $controller
     *
     * @throws LackOfCoffeeException
     * @throws InvalidArgumentException
     */
    public function __construct($controller)
    {
        parent::__construct();

        Arguments::define(Boa::string())->check($controller);

        $this->controller = $controller;
        $this->middleware = [];
        $this->methods = [];
        $this->prefix = null;
        $this->name = 'Unknown';
        $this->description = 'This resource does not provide a description.';
    }

    /**
     * @param string $controller
     *
     * @return static
     */
    public static function create($controller)
    {
        return new static($controller);
    }

    /**
     * Add middleware to use.
     *
     * @param array $middleware
     *
     * @throws InvalidArgumentException
     * @return $this
     */
    public function withMiddleware(array $middleware)
    {
        Arguments::define(Boa::arrOf(Boa::string()))->check($middleware);

        $this->middleware += $middleware;

        return $this;
    }

    /**
     * @param string $path
     * @param string $method
     * @param array $where
     *
     * @return $this
     */
    public function get($path, $method, $where = [])
    {
        $this->methods[] = new ResourceMethod($method, HttpMethods::GET, $path, $where);

        return $this;
    }

    /**
     * @param string $path
     * @param string $method
     * @param array $where
     *
     * @return $this
     */
    public function post($path, $method, $where = [])
    {
        $this->methods[] = new ResourceMethod(
            $method,
            HttpMethods::POST,
            $path,
            $where
        );

        return $this;
    }

    /**
     * @param string $path
     * @param string $method
     * @param array $where
     *
     * @return $this
     */
    public function put($path, $method, $where = [])
    {
        $this->methods[] = new ResourceMethod($method, HttpMethods::PUT, $path, $where);

        return $this;
    }

    /**
     * @param string $path
     * @param string $method
     * @param array $where
     *
     * @return $this
     */
    public function delete($path, $method, $where = [])
    {
        $this->methods[] = new ResourceMethod(
            $method,
            HttpMethods::DELETE,
            $path,
            $where
        );

        return $this;
    }

    /**
     * Inject routes into the provided router.
     *
     * @param Router $router
     * @return Router
     */
    public function inject(Router $router)
    {
        $router->group(Arr::filterNullValues([
            'middleware' => $this->middleware,
            'prefix' => $this->prefix,
        ]), function (Router $router) {
            Std::each(function (ResourceMethod $method) use ($router) {
                $handler = vsprintf('%s@%s', [
                    $this->controller,
                    $method->getMethod(),
                ]);

                $router->match(
                    [$method->getVerb()],
                    $method->getPath(),
                    $handler
                );
                $where = $method->getWhere();
                if (!empty($where)) {
                    $r->where($where);
                }
            }, $this->methods);
        });

        return $router;
    }

    /**
     * @param string $prefix
     *
     * @return $this
     */
    public function withPrefix($prefix)
    {
        Arguments::define(Boa::string())->check($prefix);

        $this->prefix = $prefix;

        return $this;
    }

    /**
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @return array
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * @return ResourceMethod[]
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @return null|string
     */
    public function getPrefix()
    {
        return $this->prefix;
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
     * @param string $name
     *
     * @return ResourceFactory
     */
    public function name($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $description
     *
     * @return ResourceFactory
     */
    public function description($description)
    {
        $this->description = $description;

        return $this;
    }
}
