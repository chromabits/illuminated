<?php

namespace Chromabits\Illuminated\Jobs\Views;

use Chromabits\Illuminated\Conference\Entities\ConferenceContext;
use Chromabits\Illuminated\Conference\Views\ContextAnchor;
use Chromabits\Illuminated\Jobs\Job;
use Chromabits\Illuminated\Jobs\Modules\JobsModule;
use Chromabits\Nucleus\Data\ArrayList;
use Chromabits\Nucleus\View\BaseHtmlRenderable;
use Chromabits\Nucleus\View\Bootstrap\CardBlock;
use Chromabits\Nucleus\View\Bootstrap\SimpleTable;
use Chromabits\Nucleus\View\Common\Italic;
use Chromabits\Nucleus\View\Common\Paragraph;
use SellerLabs\Slapp\Support\Views\ValuePresenter;

/**
 * Class ConferenceJobTablePresenter
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Jobs\Views
 */
class ConferenceJobTablePresenter extends BaseHtmlRenderable
{
    /**
     * @var ConferenceContext
     */
    protected $context;

    /**
     * @var Job[]
     */
    protected $jobs;

    /**
     * Construct an instance of a ConferenceJobTablePresenter.
     *
     * @param ConferenceContext $context
     * @param Job[] $jobs
     */
    public function __construct(ConferenceContext $context, $jobs)
    {
        parent::__construct();

        $this->context = $context;
        $this->jobs = $jobs;
    }

    /**
     * Render the object into a string.
     *
     * @return mixed
     */
    public function render()
    {
        $jobs = ArrayList::of($this->jobs);

        if ($jobs->count() === 0) {
            return $this->renderEmpty();
        }

        return new SimpleTable(
            ['ID', 'Task', 'State', 'Runs', 'Created At', 'Duration'],
            $jobs
                ->map(function (Job $job) {
                    return [
                        (new ContextAnchor($this->context))
                            ->withContent((string) $job->id)
                            ->withModule(JobsModule::NAME)
                            ->withMethod('single')
                            ->withParameters(['id' => $job->id]),
                        (new ContextAnchor($this->context))
                            ->withContent(new ValuePresenter($job->task))
                            ->withModule(JobsModule::NAME)
                            ->withMethod('reference.single')
                            ->withParameters(['id' => $job->task]),
                        new ValuePresenter(new JobStatePresenter($job)),
                        new ValuePresenter($job->attempts),
                        new ValuePresenter($job->created_at),
                        $job->getExecutionTime(),
                    ];
                })
                ->toArray()
        );
    }

    /**
     * @return CardBlock
     */
    protected function renderEmpty()
    {
        return new CardBlock(
            ['class' => 'card-block text-xs-center'],
            [
                new Paragraph([], [
                    new Italic(
                        ['class' => 'fa fa-4x fa-search text-light']
                    ),
                ]),
                'No jobs found matching the specified criteria.',
            ]
        );
    }
}
