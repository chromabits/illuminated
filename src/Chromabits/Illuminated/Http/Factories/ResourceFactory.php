<?php

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
 * Class ResourceFactory
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

        Arguments::contain(Boa::string())->check($controller);

        $this->controller = $controller;
        $this->middleware = [];
        $this->methods = [];
        $this->prefix = null;
    }

    /**
     * Add middleware to use.
     *
     * @param array $middleware
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function withMiddleware(array $middleware)
    {
        Arguments::contain(Boa::arrOf(Boa::string()))->check($middleware);

        $this->middleware += $middleware;

        return $this;
    }

    /**
     * @param string $path
     * @param string $method
     *
     * @return $this
     */
    public function get($path, $method)
    {
        $this->methods[] = new ResourceMethod($method, HttpMethods::GET, $path);

        return $this;
    }

    /**
     * @param string $path
     * @param string $method
     *
     * @return $this
     */
    public function post($path, $method)
    {
        $this->methods[] = new ResourceMethod(
            $method,
            HttpMethods::POST,
            $path
        );

        return $this;
    }

    /**
     * @param string $path
     * @param string $method
     *
     * @return $this
     */
    public function put($path, $method)
    {
        $this->methods[] = new ResourceMethod($method, HttpMethods::PUT, $path);

        return $this;
    }

    /**
     * @param string $path
     * @param string $method
     *
     * @return $this
     */
    public function delete($path, $method)
    {
        $this->methods[] = new ResourceMethod(
            $method,
            HttpMethods::DELETE,
            $path
        );

        return $this;
    }

    /**
     * Inject routes into the provided router.
     *
     * @param Router $router
     */
    public function inject(Router $router)
    {
        $router->group(Arr::filterNullValues([
            'middleware' => $this->middleware,
            'prefix' => $this->prefix,
        ]), function (Router $router) {
            Std::each(function ($key, ResourceMethod $method) use ($router) {
                $handler = vsprintf('%s@%s', [
                    $this->controller,
                    $method->getMethod()
                ]);

                $router->match(
                    [$method->getVerb()],
                    $method->getPath(),
                    $handler
                );
            }, $this->methods);
        });
    }

    /**
     * @param string $prefix
     *
     * @return $this
     */
    public function withPrefix($prefix)
    {
        Arguments::contain(Boa::string())->check($prefix);

        $this->prefix = $prefix;

        return $this;
    }
}
