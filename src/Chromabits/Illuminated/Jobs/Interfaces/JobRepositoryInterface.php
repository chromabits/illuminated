<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Jobs\Interfaces;

use Chromabits\Illuminated\Database\Interfaces\BaseRepositoryInterface;
use Chromabits\Illuminated\Jobs\Job;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\Paginator;

/**
 * Interface JobRepositoryInterface.
 *
 * Handles common operations on Jobs.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Jobs\Interfaces
 */
interface JobRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get a paginated list of jobs.
     *
     * @param int $take
     *
     * @return Paginator
     */
    public function getPaginated($take = 25);

    /**
     * Find a specific job.
     *
     * @param mixed $jobId
     *
     * @throws ModelNotFoundException
     * @return Job
     */
    public function find($jobId);

    /**
     * Delete a specific job.
     *
     * @param mixed $jobId
     *
     * @return mixed
     */
    public function delete($jobId);

    /**
     * Mark a job as failed.
     *
     * @param Job $job
     * @param string|null $message
     */
    public function fail(Job $job, $message = null);

    /**
     * Mark a job as completed.
     *
     * @param Job $job
     * @param string|null $message
     */
    public function complete(Job $job, $message = null);

    /**
     * Mark a job as running.
     *
     * @param Job $job
     * @param string|null $message
     */
    public function started(Job $job, $message = null);

    /**
     * Mark a job as released.
     *
     * This is occurs when a job has failed in the past and it still has
     * attempts remaining.
     *
     * @param Job $job
     * @param null $message
     */
    public function release(Job $job, $message = null);

    /**
     * Make sure this job does not run again even if it has retries left.
     *
     * This should be used when a problem is detected with the way the job or
     * task are coded which prevents them from running correctly.
     *
     * @param Job $job
     * @param null $message
     */
    public function giveUp(Job $job, $message = null);

    /**
     * Get a paginated list of scheduled jobs.
     *
     * @param int $take
     *
     * @return Paginator
     */
    public function getScheduledPaginated($take = 25);

    /**
     * Get a paginated list of failed jobs.
     *
     * @param int $take
     *
     * @return Paginator
     */
    public function getFailedPaginated($take = 25);

    /**
     * Get a paginated list of queued jobs.
     *
     * @param int $take
     *
     * @return Paginator
     */
    public function getQueuedPaginated($take = 25);
}
