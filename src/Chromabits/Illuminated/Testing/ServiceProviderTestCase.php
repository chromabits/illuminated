<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Testing;

use Chromabits\Nucleus\Meditation\Primitives\CompoundTypes;
use Chromabits\Nucleus\Testing\TestCase;
use Illuminate\Console\Application as ConsoleApplication;
use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Mockery as m;

/**
 * Class ServiceProviderTestCase.
 *
 * A base class for tests testing service providers.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Testing
 */
abstract class ServiceProviderTestCase extends TestCase
{
    /**
     * List of abstracts that should be bound.
     *
     * @var array
     */
    protected $shouldBeBound = [];

    /**
     * List of commands that should be registered by the provider.
     *
     * @var array
     */
    protected $commands = [];

    /**
     * Get list of abstracts that should be bound.
     *
     * @return array
     */
    protected function getExpectedBindings()
    {
        return $this->shouldBeBound;
    }

    /**
     * Get list of commands that should be registered by the provider.
     *
     * @return array
     */
    protected function getCommands()
    {
        return $this->commands;
    }

    /**
     * Construct an instance of a ServiceProviderTestCase.
     *
     * @param null $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->mockApp = new Application();
    }

    /**
     * Make an instance of the service provider being tested.
     *
     * @param Application $app
     *
     * @return ServiceProvider
     */
    abstract protected function make(Application $app);

    public function testRegister()
    {
        $instance = $this->make($this->mockApp);

        $instance->register();

        foreach ($this->getExpectedBindings() as $abstract) {
            $this->assertTrue(
                $this->mockApp->bound($abstract),
                $abstract . 'should be bound.'
            );
        }
    }

    public function testRegisterCommands()
    {
        if (count($this->getCommands()) == 0) {
            return;
        }

        $app = new Application();

        $provider = $this->make($app);
        $provider->register();

        $recordedCommands = [];
        $artisan = m::mock(ConsoleApplication::class);
        $artisan->shouldReceive('resolveCommands')
            ->with(m::on(function ($input) use (&$recordedCommands) {
                $recordedCommands = $input;

                return is_array($input);
            }))
            ->once();

        /** @var Dispatcher $events */
        $events = $app['events'];
        $events->fire('artisan.start', [
            'artisan' => $artisan,
        ]);

        $this->assertEquals($this->getCommands(), $recordedCommands);
    }

    public function testProvides()
    {
        $instance = $this->make($this->mockApp);

        $result = $instance->provides();

        $this->assertInternalType(CompoundTypes::COMPOUND_ARRAY, $result);

        if ($instance->isDeferred()) {
            foreach ($this->getExpectedBindings() as $abstract) {
                $this->assertTrue(in_array($abstract, $result));
            }
        }
    }
}
