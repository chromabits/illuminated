<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Conference;

use Chromabits\Illuminated\Conference\Controllers\ConferenceController;
use Chromabits\Illuminated\Http\Factories\ResourceFactory;
use Chromabits\Illuminated\Http\Interfaces\RouteMapper;
use Chromabits\Nucleus\Foundation\BaseObject;
use Illuminate\Routing\Router;

/**
 * Class ConferenceRouteDirectory.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Conference
 */
class ConferenceRouteDirectory extends BaseObject implements RouteMapper
{
    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var string[]
     */
    protected $middleware;

    /**
     * Construct an instance of a ConferenceRouteDirectory.
     *
     * @param string $prefix
     * @param string[] $middleware
     */
    public function __construct($prefix = '/conference', $middleware = [])
    {
        parent::__construct();

        $this->prefix = $prefix;
        $this->middleware = $middleware;
    }

    /**
     * Map routes.
     *
     * @param Router $router
     */
    public function map(Router $router)
    {
        ResourceFactory::create(ConferenceController::class)
            ->withPrefix($this->prefix)
            ->withMiddleware($this->middleware)
            ->get('/', 'anyIndex')
            ->get('/css/main.css', 'getCss')
            ->get('/{moduleName}', 'anyModule')
            ->post('/{moduleName}', 'anyModule')
            ->get('/{moduleName}/{methodName}', 'anyMethod')
            ->post('/{moduleName}/{methodName}', 'anyMethod')
            ->inject($router);
    }
}
