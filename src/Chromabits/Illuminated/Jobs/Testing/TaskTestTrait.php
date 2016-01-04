<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Jobs\Testing;

use Chromabits\Illuminated\Jobs\Tasks\BaseTask;
use Chromabits\Nucleus\Exceptions\LackOfCoffeeException;
use Chromabits\Nucleus\Meditation\Spec;
use Chromabits\Nucleus\Support\Std;

/**
 * Class TaskTestTrait.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Jobs\Testing
 */
trait TaskTestTrait
{
    /**
     * Safely attempt to create an instance of the task being tested.
     *
     * @throws LackOfCoffeeException
     * @return BaseTask
     */
    protected function makeTaskSafely()
    {
        if (!method_exists($this, 'make')) {
            throw new LackOfCoffeeException(vsprintf(
                'The test case %s is missing a make() function. You should' .
                ' try adding a make() that returns an instance of the task' .
                ' being tested.',
                [static::class]
            ));
        }

        $instance = $this->make();

        if (!$instance instanceof BaseTask) {
            throw new LackOfCoffeeException(vsprintf(
                'The class %s is not an instance of %s',
                [get_class($instance), BaseTask::class]
            ));
        }

        return $instance;
    }

    public function testGetSpec()
    {
        $spec = $this->makeTaskSafely()->getSpec();

        $this->assertTrue(Std::truthy(
            $spec === null,
            $spec instanceof Spec
        ));
    }

    public function testGetReference()
    {
        $task = $this->makeTaskSafely();

        $this->assertInternalType('array', $task->getReference());
    }

    public function testGetTypes()
    {
        $task = $this->makeTaskSafely();

        $this->assertInternalType('array', $task->getTypes());
    }

    public function testGetDefaults()
    {
        $task = $this->makeTaskSafely();

        $this->assertInternalType('array', $task->getDefaults());
    }

    public function testGetDescription()
    {
        $task = $this->makeTaskSafely();

        $this->assertInternalType('string', $task->getDescription());
    }
}
