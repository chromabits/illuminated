<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Jobs\Tasks;

use Chromabits\Illuminated\Jobs\Interfaces\JobSchedulerInterface;
use Chromabits\Illuminated\Jobs\Job;
use Chromabits\Nucleus\Exceptions\LackOfCoffeeException;
use Chromabits\Nucleus\Foundation\BaseObject;
use Chromabits\Nucleus\Meditation\Constraints\AbstractConstraint;
use Chromabits\Nucleus\Meditation\Spec;
use Chromabits\Nucleus\Support\Std;

/**
 * Class BaseTask.
 *
 * A base class for a task.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Jobs\Tasks
 */
abstract class BaseTask extends BaseObject
{
    /**
     * Description of the task.
     *
     * @var string
     */
    protected $description = 'No description available.';

    /**
     * Spec to use for input validation.
     *
     * @var Spec|null
     */
    protected $spec = null;

    /**
     * Should jobs be deleted when they are completed
     *
     * @var bool
     */
    protected $selfDeleting = false;

    /**
     * Construct an instance of a BaseTask.
     *
     * @throws LackOfCoffeeException
     */
    public function __construct()
    {
        parent::__construct();

        $this->spec = $this->getSpec();
    }

    /**
     * Process a job.
     *
     * Note: A handler implementation does not need to worry about exception
     * handling and retries. All this is automatically managed by the task
     * runner.
     *
     * @param Job $job
     * @param JobSchedulerInterface $scheduler
     */
    abstract public function fire(Job $job, JobSchedulerInterface $scheduler);

    /**
     * Get the Spec to use for input validation.
     *
     * @return Spec|null
     */
    public function getSpec()
    {
        return null;
    }

    /**
     * Get a simple array mapping accepted data keys with a description for
     * providing a barebones help section.
     *
     * @return array
     */
    public function getReference()
    {
        return [];
    }

    /**
     * Get the type of each field.
     *
     * @return array
     */
    public function getTypes()
    {
        if ($this->spec instanceof Spec) {
            return Std::map(function ($field) {
                if ($field instanceof AbstractConstraint) {
                    return $field->toString();
                }

                return $field
                    ->map(function ($innerField) {
                        if ($innerField instanceof Spec) {
                            return '{spec}';
                        } elseif ($innerField instanceof AbstractConstraint) {
                            return $innerField->toString();
                        }

                        return '{???}';
                    })
                    ->join(' ^ ');
            }, $this->spec->getConstraints());
        }

        return [];
    }

    /**
     * Get the default value of each field.
     *
     * @return array
     */
    public function getDefaults()
    {
        if ($this->spec instanceof Spec) {
            return $this->spec->getDefaults();
        }

        return [];
    }

    /**
     * Get the task description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Whether the jobs should be deleted after completing
     *
     * @return bool
     */
    public function isSelfDeleting()
    {
        return $this->selfDeleting;
    }
}
