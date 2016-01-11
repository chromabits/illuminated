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

use Chromabits\Illuminated\Conference\Entities\ConferenceContext;
use Chromabits\Illuminated\Conference\Entities\SidebarPanelPair;
use Chromabits\Illuminated\Conference\Exceptions\MethodNotFoundException;
use Chromabits\Illuminated\Conference\Exceptions\ModuleNotFoundException;
use Chromabits\Illuminated\Conference\Interfaces\DashboardInterface;
use Chromabits\Nucleus\Exceptions\CoreException;
use Chromabits\Nucleus\Foundation\BaseObject;
use Chromabits\Nucleus\Meditation\Arguments;
use Chromabits\Nucleus\Meditation\Boa;
use Chromabits\Nucleus\Meditation\Exceptions\InvalidArgumentException;
use Chromabits\Nucleus\Support\Arr;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;

/**
 * Class Dashboard.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Conference
 */
class Dashboard extends BaseObject implements DashboardInterface
{
    /**
     * The default module used by the dashboard.
     */
    const DEFAULT_MODULE = 'illuminated.conference.front';

    /**
     * @var Application
     */
    protected $application;

    /**
     * @var Module[]
     */
    protected $modules;

    /**
     * @var Exception[]
     */
    protected $failedModules;

    /**
     * Construct an instance of a Dashboard.
     *
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        parent::__construct();

        $this->application = $application;

        $this->modules = [];
        $this->failedModules = [];
    }

    /**
     * @return Module[]
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * Register a module with this dashboard.
     *
     * @param string $moduleClassName
     *
     * @throws CoreException
     * @throws InvalidArgumentException
     */
    public function register($moduleClassName)
    {
        Arguments::define(Boa::string())->check($moduleClassName);

        $instance = $this->application->make($moduleClassName);

        if (!$instance instanceof Module) {
            throw new InvalidArgumentException(
                'The provided class should extend the Module class.'
            );
        }

        try {
            $instance->boot();

            $this->modules[$instance->getName()] = $instance;
        } catch (Exception $e) {
            $this->failedModules[$instance->getName()] = $e;
        }
    }

    /**
     * Get the names of all the registered modules.
     *
     * @return string[]
     */
    public function getModuleNames()
    {
        return Arr::keys($this->modules);
    }

    /**
     * Process an incoming request through the dashboard.
     *
     * @param Request $request
     * @param ConferenceContext $context
     * @param null|string $module
     * @param null|string $method
     *
     * @throws MethodNotFoundException
     * @throws ModuleNotFoundException
     * @return SidebarPanelPair
     */
    public function run(
        Request $request,
        ConferenceContext $context,
        $module = null,
        $method = null
    ) {
        $module = $this->resolveModule($module);

        return new SidebarPanelPair(
            $module->renderSidebar($context),
            $this
                ->resolveMethod($module, $method)
                ->setContainer($this->application)
                ->run($request)
        );
    }

    /**
     * Resolve a module by name.
     *
     * @param null $moduleName
     *
     * @throws ModuleNotFoundException
     * @return Module
     */
    protected function resolveModule($moduleName = null)
    {
        $selected = $moduleName;

        if ($moduleName === null) {
            $selected = static::DEFAULT_MODULE;
        }

        if (!Arr::has($this->modules, $selected)) {
            throw new ModuleNotFoundException($selected);
        }

        return $this->modules[$selected];
    }

    /**
     * Resolve a method inside a module.
     *
     * @param Module $module
     * @param string|null $methodName
     *
     * @throws MethodNotFoundException
     * @return mixed
     */
    protected function resolveMethod(Module $module, $methodName = null)
    {
        $selected = $methodName;

        if ($methodName === null) {
            $selected = $module->getDefaultMethodName();
        }

        if (!$module->hasMethod($selected)) {
            throw new MethodNotFoundException($module->getName(), $methodName);
        }

        return $module->getMethod($selected);
    }

    /**
     * @return Exception[]
     */
    public function getFailedModules()
    {
        return $this->failedModules;
    }
}
