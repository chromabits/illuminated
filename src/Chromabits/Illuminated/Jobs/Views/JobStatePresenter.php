<?php

namespace Chromabits\Illuminated\Jobs\Views;

use Chromabits\Illuminated\Jobs\Job;
use Chromabits\Illuminated\Jobs\JobState;
use Chromabits\Nucleus\View\BaseHtmlRenderable;
use Chromabits\Nucleus\View\Common\Span;

/**
 * Class JobStatePresenter
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Jobs\Views
 */
class JobStatePresenter extends BaseHtmlRenderable
{
    /**
     * @var Job
     */
    protected $job;

    /**
     * Construct an instance of a JobStatePresenter.
     *
     * @param Job $job
     */
    public function __construct(Job $job)
    {
        parent::__construct();

        $this->job = $job;
    }

    /**
     * Render the object into a string.
     *
     * @return mixed
     */
    public function render()
    {
        switch ($this->job->state) {
            case JobState::SCHEDULED:
                return new Span(
                    ['class' => 'label label-info'],
                    'Scheduled'
                );
            case JobState::QUEUED:
                return new Span(
                    ['class' => 'label label-info'],
                    'Queued'
                );
            case JobState::RUNNING:
                return new Span(
                    ['class' => 'label label-primary'],
                    'Running'
                );
            case JobState::COMPLETE:
                return new Span(
                    ['class' => 'label label-success'],
                    'Complete'
                );
            case JobState::CANCELLED:
                return new Span(
                    ['class' => 'label label-warning'],
                    'Cancelled'
                );
            case JobState::FAILED:
                return new Span(
                    ['class' => 'label label-danger'],
                    'Failed'
                );
            default:
                return new Span(
                    ['class' => 'label label-default'],
                    $this->job->state
                );
        }
    }
}
