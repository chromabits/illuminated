<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Jobs\Controllers;

use Carbon\Carbon;
use Chromabits\Illuminated\Conference\Entities\ConferenceContext;
use Chromabits\Illuminated\Conference\Views\ConferenceConfirmationCard;
use Chromabits\Illuminated\Conference\Views\ConferencePaginator;
use Chromabits\Illuminated\Conference\Views\ConferenceWideContainer;
use Chromabits\Illuminated\Conference\Views\FormSpecPresenter;
use Chromabits\Illuminated\Http\BaseController;
use Chromabits\Illuminated\Jobs\Interfaces\HandlerResolverInterface;
use Chromabits\Illuminated\Jobs\Interfaces\JobFactoryInterface;
use Chromabits\Illuminated\Jobs\Interfaces\JobRepositoryInterface;
use Chromabits\Illuminated\Jobs\Interfaces\JobSchedulerInterface;
use Chromabits\Illuminated\Jobs\Job;
use Chromabits\Illuminated\Jobs\Modules\JobsModule;
use Chromabits\Illuminated\Jobs\Requests\CancelJobModuleRequest;
use Chromabits\Illuminated\Jobs\Requests\CreateJobModuleRequest;
use Chromabits\Illuminated\Jobs\Requests\DescribeJobModuleRequest;
use Chromabits\Illuminated\Jobs\Views\ConferenceCreateJobButton;
use Chromabits\Illuminated\Jobs\Views\ConferenceJobDetailsPresenter;
use Chromabits\Illuminated\Jobs\Views\ConferenceJobTablePresenter;
use Chromabits\Nucleus\Http\Enums\HttpMethods;
use Chromabits\Nucleus\Support\Arr;
use Chromabits\Nucleus\Support\Std;
use Chromabits\Nucleus\View\Bootstrap\Card;
use Chromabits\Nucleus\View\Bootstrap\CardBlock;
use Chromabits\Nucleus\View\Bootstrap\CardHeader;
use Chromabits\Nucleus\View\Bootstrap\Column;
use Chromabits\Nucleus\View\Bootstrap\Row;
use Chromabits\Nucleus\View\Bootstrap\SimpleTable;
use Chromabits\Nucleus\View\Common\Anchor;
use Chromabits\Nucleus\View\Common\Div;
use Chromabits\Nucleus\View\Common\HeaderOne;
use Chromabits\Nucleus\View\Common\HeaderSix;
use Chromabits\Nucleus\View\Common\Paragraph;
use Chromabits\Nucleus\View\Common\PreformattedText;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

/**
 * Class JobsModuleController.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Jobs\Controllers
 */
class JobsModuleController extends BaseController
{
    /**
     * @var JobRepositoryInterface
     */
    protected $jobs;

    /**
     * @var JobSchedulerInterface
     */
    protected $scheduler;

    /**
     * @var HandlerResolverInterface
     */
    protected $resolver;

    /**
     * @var JobFactoryInterface
     */
    protected $factory;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ConferenceContext
     */
    protected $context;

    /**
     * Construct an instance of a JobsModuleController.
     *
     * @param ConferenceContext $context
     * @param JobRepositoryInterface $jobs
     * @param JobSchedulerInterface $scheduler
     * @param HandlerResolverInterface $resolver
     * @param JobFactoryInterface $factory
     * @param Request $request
     */
    public function __construct(
        ConferenceContext $context,
        JobRepositoryInterface $jobs,
        JobSchedulerInterface $scheduler,
        HandlerResolverInterface $resolver,
        JobFactoryInterface $factory,
        Request $request
    ) {
        $this->context = $context;
        $this->jobs = $jobs;
        $this->scheduler = $scheduler;
        $this->resolver = $resolver;
        $this->factory = $factory;
        $this->request = $request;
    }

    /**
     * Get all jobs.
     *
     * @return Card
     */
    public function getIndex()
    {
        $jobs = $this->jobs->getPaginated();

        return new Card([], [
            new CardHeader([], [
                new Row([], [
                    new Column(['medium' => 6, 'class' => 'btn-y-align'], [
                        'All Jobs',
                    ]),
                    new ConferenceCreateJobButton($this->context),
                ]),
            ]),
            new ConferenceJobTablePresenter($this->context, $jobs->items()),
            new ConferencePaginator($jobs),
        ]);
    }

    /**
     * Describe a single job.
     *
     * @param DescribeJobModuleRequest $request
     * @param JobRepositoryInterface $jobRepository
     *
     * @return Div
     */
    public function getSingle(
        DescribeJobModuleRequest $request,
        JobRepositoryInterface $jobRepository
    ) {
        $job = $jobRepository->getById((int) $request->get('id'));

        return new Div([], [
            new ConferenceJobDetailsPresenter($this->context, $job),
        ]);
    }

    /**
     * Get form for creating a new task.
     *
     * @return Div
     */
    public function getCreate()
    {
        return new Div([], [
            new Card([], [
                new CardHeader([], 'Create a new job'),
                new CardBlock([], [
                    new FormSpecPresenter(
                        CreateJobModuleRequest::getFormSpec($this->resolver),
                        [
                            'method' => HttpMethods::POST,
                            'action' => $this->context
                                ->method(JobsModule::NAME, 'create.post'),
                        ]
                    ),
                ]),
            ]),
        ]);
    }

    /**
     * Create a new job.
     *
     * @param CreateJobModuleRequest $request
     * @param JobFactoryInterface $jobFactory
     * @param JobSchedulerInterface $jobScheduler
     * @param Redirector $redirector
     *
     * @return string
     */
    public function postCreate(
        CreateJobModuleRequest $request,
        JobFactoryInterface $jobFactory,
        JobSchedulerInterface $jobScheduler,
        Redirector $redirector
    ) {
        $job = $jobFactory->make(
            $request->get('task'),
            $request->get('payload'),
            (int) $request->get('retries')
        );

        if ($request->request->has('expire_at')) {
            $job->expires_at = Carbon::parse(
                $request->request->get('expire_at')
            );
        }

        $jobScheduler->push($job, Carbon::parse($request->get('run_at')));

        return $redirector->to($this->context->method(
            JobsModule::NAME,
            'single',
            ['id' => $job->id]
        ));
    }

    /**
     * Index all scheduled jobs.
     *
     * @return Card
     */
    public function getScheduled()
    {
        $jobs = $this->jobs->getScheduledPaginated();

        return new Card([], [
            new CardHeader([], [
                new Row([], [
                    new Column(['medium' => 6, 'class' => 'btn-y-align'], [
                        'Scheduled Jobs',
                    ]),
                    new ConferenceCreateJobButton($this->context),
                ]),
            ]),
            new ConferenceJobTablePresenter($this->context, $jobs->items()),
            new ConferencePaginator($jobs),
        ]);
    }

    /**
     * Index all queued jobs.
     *
     * @return Card
     */
    public function getQueued()
    {
        $jobs = $this->jobs->getQueuedPaginated();

        return new Card([], [
            new CardHeader([], [
                new Row([], [
                    new Column(['medium' => 6, 'class' => 'btn-y-align'], [
                        'Queued Jobs',
                    ]),
                    new ConferenceCreateJobButton($this->context),
                ]),
            ]),
            new ConferenceJobTablePresenter($this->context, $jobs->items()),
            new ConferencePaginator($jobs),
        ]);
    }

    /**
     * Index all failed jobs.
     *
     * @return Card
     */
    public function getFailed()
    {
        $jobs = $this->jobs->getFailedPaginated();

        return new Card([], [
            new CardHeader([], [
                new Row([], [
                    new Column(['medium' => 6, 'class' => 'btn-y-align'], [
                        'Failed Jobs',
                    ]),
                    new ConferenceCreateJobButton($this->context),
                ]),
            ]),
            new ConferenceJobTablePresenter($this->context, $jobs->items()),
            new ConferencePaginator($jobs),
        ]);
    }

    /**
     * Index all available tasks.
     *
     * @param HandlerResolverInterface $resolver
     * @param ConferenceContext $context
     *
     * @return Card
     */
    public function getReference(
        HandlerResolverInterface $resolver,
        ConferenceContext $context
    ) {
        return new Card([], [
            new CardHeader([], 'Task Reference'),
            new SimpleTable(
                ['Task', 'Description'],
                Std::map(function ($taskName) use ($resolver, $context) {
                    $instance = $resolver->instantiate($taskName);

                    return [
                        new Anchor([
                            'href' => $context->method(
                                'illuminated.jobs',
                                'reference.single',
                                ['id' => $taskName]
                            ),
                        ], $taskName),
                        $instance->getDescription(),
                    ];
                },
                    $resolver->getAvailableTasks())
            ),
        ]);
    }

    /**
     * Describe a single task.
     *
     * @param HandlerResolverInterface $resolver
     * @param ConferenceContext $context
     * @param Request $request
     *
     * @return Card
     */
    public function getReferenceSingle(
        HandlerResolverInterface $resolver,
        ConferenceContext $context,
        Request $request
    ) {
        $taskName = $request->query->get('id');
        $handler = $resolver->instantiate($taskName);
        $defaults = $handler->getDefaults();
        $types = $handler->getTypes();

        return new Card([], [
            new CardHeader([], 'Reference for task:'),
            new CardBlock([], [
                new HeaderOne(['class' => 'display-one'], $taskName),
                new Paragraph(['class' => 'lead'], $handler->getDescription()),
            ]),
            new SimpleTable(
                ['Field Name', 'Type', 'Default', 'Description'],
                Std::map(
                    function ($description, $field) use ($types, $defaults) {
                        return [
                            $field,
                            Arr::dotGet($types, $field, '-'),
                            (string) Arr::dotGet($defaults, $field, '-'),
                            $description,
                        ];
                    },
                    $handler->getReference()
                )
            ),
            new CardBlock([], [
                new HeaderSix([], 'Example usage:'),
                new PreformattedText(
                    [],
                    json_encode($defaults, JSON_PRETTY_PRINT)
                ),
            ]),
        ]);
    }

    /**
     * Cancel a job.
     *
     * @param CancelJobModuleRequest $request
     * @param JobRepositoryInterface $jobRepository
     * @param JobSchedulerInterface $jobScheduler
     * @param Redirector $redirector
     *
     * @return ConferenceWideContainer|RedirectResponse
     */
    public function getCancel(
        CancelJobModuleRequest $request,
        JobRepositoryInterface $jobRepository,
        JobSchedulerInterface $jobScheduler,
        Redirector $redirector
    ) {
        /** @var Job $job */
        $job = $jobRepository->getById((int) $request->get('id'));

        if ($request->get('confirm')) {
            $jobScheduler->cancel($job);

            return $redirector->to($this->context->method(
                JobsModule::NAME,
                'single',
                ['id' => $job->id]
            ));
        }

        return new ConferenceWideContainer(
            new Row([], [
                new Column(['class' => 'col-md-6 col-md-offset-3'], [
                    (new ConferenceConfirmationCard($this->context))
                        ->withDescription(vsprintf(
                            'Cancel job with ID %s (Task: %s)',
                            [$job->id, $job->task]
                        )),
                ]),
            ])
        );
    }
}
