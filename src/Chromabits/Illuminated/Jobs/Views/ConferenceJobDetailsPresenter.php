<?php

namespace Chromabits\Illuminated\Jobs\Views;

use Chromabits\Illuminated\Conference\Entities\ConferenceContext;
use Chromabits\Illuminated\Conference\Views\ContentMenuPresenter;
use Chromabits\Illuminated\Conference\Views\ContextAnchor;
use Chromabits\Illuminated\Jobs\Job;
use Chromabits\Illuminated\Jobs\Modules\JobsModule;
use Chromabits\Nucleus\Exceptions\LackOfCoffeeException;
use Chromabits\Nucleus\View\BaseHtmlRenderable;
use Chromabits\Nucleus\View\Bootstrap\Card;
use Chromabits\Nucleus\View\Bootstrap\CardBlock;
use Chromabits\Nucleus\View\Bootstrap\CardHeader;
use Chromabits\Nucleus\View\Common\Bold;
use Chromabits\Nucleus\View\Common\Code;
use Chromabits\Nucleus\View\Common\HorizontalLine;
use Chromabits\Nucleus\View\Common\PreformattedText;
use Chromabits\Illuminated\Conference\Views\ValueList;
use Chromabits\Illuminated\Conference\Views\ValuePresenter;

/**
 * Class ConferenceJobDetailsPresenter
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Jobs\Views
 */
class ConferenceJobDetailsPresenter extends BaseHtmlRenderable
{
    /**
     * @var ConferenceContext
     */
    protected $context;

    /**
     * @var Job
     */
    protected $job;

    /**
     * Construct an instance of a ConferenceJobDetailsPresenter.
     *
     * @param ConferenceContext $context
     * @param Job $job
     *
     * @throws LackOfCoffeeException
     */
    public function __construct(ConferenceContext $context, Job $job)
    {
        parent::__construct();

        $this->context = $context;
        $this->job = $job;
    }

    /**
     * Render the object into a string.
     *
     * @return mixed
     */
    public function render()
    {
        $job = $this->job;

        return new Card([], [
            new CardHeader([], 'Job details'),
            new CardBlock([], [
                $this->withMenu(
                    (new ContentMenuPresenter($this->context))
                        ->withContent(
                            (new ValueList())
                                ->addTerm('ID', $job->id)
                                ->addTerm(
                                    'Task',
                                    (new ContextAnchor($this->context))
                                        ->withContent(
                                            new ValuePresenter($job->task)
                                        )
                                        ->withModule(JobsModule::NAME)
                                        ->withMethod('reference.single')
                                        ->withParameters(['id' => $job->task])
                                )
                                ->addTerm('Status', new JobStatePresenter($job))
                                ->addTerm('Will run after', $job->run_at)
                                ->addTerm('Expires at', $job->expires_at)
                                ->addTerm('Started at', $job->started_at)
                                ->addTerm('Completed at', $job->completed_at)
                                ->addTerm(
                                    'Execution time',
                                    $job->getExecutionTime()
                                )
                                ->addTerm('Attempts', $job->attempts)
                                ->addTerm('Retries', $job->retries)
                                ->addTerm(
                                    'Queue Connection',
                                    $job->queue_connection
                                )
                                ->addTerm(
                                    'Queue Name',
                                    $job->queue_name
                                )
                                ->addTerm('Created at', $job->created_at)
                                ->addTerm('Updated at', $job->updated_at)
                        )
                ),
            ]),
            new HorizontalLine(['class' => 'm-y-0']),
            new CardBlock([], [
                new Bold([], 'Payload:'),
                new PreformattedText([], new Code(
                    [],
                    new ValuePresenter($job->data)
                )),
            ]),
            new HorizontalLine(['class' => 'm-y-0']),
            new CardBlock([], [
                new Bold([], 'Messages:'),
                new PreformattedText([], new Code(
                    [],
                    new ValuePresenter($job->message)
                )),
            ]),
        ]);
    }

    /**
     * @param ContentMenuPresenter $presenter
     *
     * @return ContentMenuPresenter
     */
    protected function withMenu(ContentMenuPresenter $presenter)
    {
        if ($this->job->isCancellable()) {
            $presenter = $presenter->withContextAction(
                'times',
                'Cancel',
                JobsModule::NAME,
                'cancel',
                ['id' => $this->job->id]
            );
        } else {
            $presenter = $presenter->withAction('times', 'Cancel');
        }

        return $presenter->withContextAction(
            'refresh',
            'Refresh',
            JobsModule::NAME,
            'single',
            ['id' => $this->job->id]
        );
    }
}
