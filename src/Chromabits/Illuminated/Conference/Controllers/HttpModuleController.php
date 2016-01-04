<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Conference\Controllers;

use Chromabits\Illuminated\Http\BaseController;
use Chromabits\Nucleus\Support\Std;
use Chromabits\Nucleus\View\Common\Div;
use Chromabits\Nucleus\View\Common\PreformattedText;
use Chromabits\Nucleus\View\Common\Table;
use Chromabits\Nucleus\View\Common\TableBody;
use Chromabits\Nucleus\View\Common\TableCell;
use Chromabits\Nucleus\View\Common\TableHeader;
use Chromabits\Nucleus\View\Common\TableHeaderCell;
use Chromabits\Nucleus\View\Common\TableRow;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;

/**
 * Class HttpModuleController.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Conference\Controllers
 */
class HttpModuleController extends BaseController
{
    /**
     * Get all routes.
     *
     * @param Router $router
     *
     * @return Div
     */
    public function getIndex(Router $router)
    {
        return (new Div([], [
            new Div(['class' => 'card'], [
                new Div(['class' => 'card-header'], 'All Routes'),
                new Div(['class' => 'card-block'], [
                    'Here are all the routes defined within this application:',
                ]),
                $this->renderRoutes($router),
            ]),
        ]));
    }

    /**
     * Get router middleware.
     *
     * @param Router $router
     *
     * @return Div
     */
    public function getRouterMiddleware(Router $router)
    {
        return (new Div([], [
            new Div(['class' => 'card'], [
                new Div(['class' => 'card-header'], 'Middleware'),
                new Div(['class' => 'card-block'], [
                    'Here is the top-level middleware stack for this ',
                    'application.',
                ]),
                $this->renderMiddleware($router),
            ]),
        ]));
    }

    /**
     * Get all router patterns.
     *
     * @param Router $router
     *
     * @return Div
     */
    public function getPatterns(Router $router)
    {
        return (new Div([], [
            new Div(['class' => 'card'], [
                new Div(['class' => 'card-header'], 'Patterns'),
                new Div(['class' => 'card-block'], [
                    'Here is the top-level patterns for this application.',
                ]),
                $this->renderPatterns($router),
            ]),
        ]));
    }

    /**
     * Render routes table.
     *
     * @param Router $router
     *
     * @return Table
     */
    protected function renderRoutes(Router $router)
    {
        return new Table(['class' => 'table'], [
            new TableHeader([], new TableRow([], [
                new TableHeaderCell([], 'URI'),
                new TableHeaderCell([], 'Action'),
            ])),
            new TableBody([], Std::map(function (Route $route) {
                return new TableRow([], [
                    new TableCell([], $route->getUri()),
                    new TableCell([], new PreformattedText(
                        ['class' => 'pre-scrollable'],
                        $route->getActionName()
                    )),
                ]);
            }, $router->getRoutes())),
        ]);
    }

    /**
     * Render middleware table.
     *
     * @param Router $router
     *
     * @return Table
     */
    protected function renderMiddleware(Router $router)
    {
        return new Table(['class' => 'table'], [
            new TableHeader([], new TableRow([], [
                new TableHeaderCell([], 'Name'),
                new TableHeaderCell([], 'Class'),
            ])),
            new TableBody([], Std::map(function ($class, $name) {
                return new TableRow([], [
                    new TableCell([], $name),
                    new TableCell([], new PreformattedText(
                        ['class' => 'pre-scrollable'],
                        $class
                    )),
                ]);
            }, $router->getMiddleware())),
        ]);
    }

    /**
     * Render all router patterns.
     *
     * @param Router $router
     *
     * @return Table
     */
    protected function renderPatterns(Router $router)
    {
        return new Table(['class' => 'table'], [
            new TableHeader([], new TableRow([], [
                new TableHeaderCell([], 'Key'),
                new TableHeaderCell([], 'Pattern'),
            ])),
            new TableBody([], Std::map(function ($pattern, $key) {
                return new TableRow([], [
                    new TableCell([], $key),
                    new TableCell([], new PreformattedText(
                        ['class' => 'pre-scrollable'],
                        $pattern
                    )),
                ]);
            }, $router->getPatterns())),
        ]);
    }
}
