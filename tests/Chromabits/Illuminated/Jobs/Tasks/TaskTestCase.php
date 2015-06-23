<?php

namespace Tests\Chromabits\Illuminated\Jobs\Tasks;

use Tests\Chromabits\Support\HelpersTestCase;

/**
 * Class TaskTestCase
 *
 * Base test case for tasks.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Tests\Chromabits\Illuminated\Jobs\Tasks
 */
abstract class TaskTestCase extends HelpersTestCase
{
    /**
     * Make an instance of the task being tested.
     *
     * @return mixed
     */
    abstract public function make();

    public function testGetReference()
    {
        $task = $this->make();

        $this->assertInternalType('array', $task->getReference());
    }

    public function testGetTypes()
    {
        $task = $this->make();

        $this->assertInternalType('array', $task->getTypes());
    }

    public function testGetDefaults()
    {
        $task = $this->make();

        $this->assertInternalType('array', $task->getDefaults());
    }

    public function testGetDescription()
    {
        $task = $this->make();

        $this->assertInternalType('string', $task->getDescription());
    }
}