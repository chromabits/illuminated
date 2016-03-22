<?php

namespace Chromabits\Illuminated\Jobs\Requests;

use Carbon\Carbon;
use Chromabits\Illuminated\Conference\ConferenceFrontCheckableRequest;
use Chromabits\Illuminated\Conference\Entities\ConferenceContext;
use Chromabits\Illuminated\Contracts\Alerts\AlertManager;
use Chromabits\Illuminated\Jobs\Interfaces\HandlerResolverInterface;
use Chromabits\Illuminated\Meditation\Constraints\DateTimeStringConstraint;
use Chromabits\Illuminated\Meditation\Constraints\ValidJsonStringConstraint;
use Chromabits\Nucleus\Meditation\Boa;
use Chromabits\Nucleus\Meditation\FormSpec;
use Chromabits\Nucleus\Meditation\Interfaces\CheckableInterface;
use Chromabits\Nucleus\Validation\Constraints\StringLengthConstraint;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\Route;

/**
 * Class CreateJobModuleRequest
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Jobs\Requests
 */
class CreateJobModuleRequest extends ConferenceFrontCheckableRequest
{
    /**
     * @var HandlerResolverInterface
     */
    protected $handlerResolver;

    /**
     * Construct an instance of a CreateJobModuleRequest.
     *
     * @param Request $request
     * @param Route $route
     * @param Application $application
     * @param AlertManager $alerts
     * @param Redirector $redirector
     * @param ConferenceContext $context
     * @param HandlerResolverInterface $handlerResolver
     */
    public function __construct(
        Request $request,
        Route $route,
        Application $application,
        AlertManager $alerts,
        Redirector $redirector,
        ConferenceContext $context,
        HandlerResolverInterface $handlerResolver
    ) {
        parent::__construct(
            $request,
            $route,
            $application,
            $alerts,
            $redirector,
            $context
        );

        $this->handlerResolver = $handlerResolver;
    }

    /**
     * @param HandlerResolverInterface $resolver
     *
     * @return FormSpec
     */
    public static function getFormSpec(HandlerResolverInterface $resolver)
    {
        $availableTasks = $resolver->getAvailableTasks();

        return (new FormSpec())
            // Task
            ->withFieldType('task', Boa::string())
            ->withFieldConstraints('task', [
                Boa::in($availableTasks),
                new StringLengthConstraint(1, 255),
            ])
            ->withFieldDefault('task', $availableTasks[0])
            ->withFieldLabel('task', 'Task')
            ->withFieldRequired('task', true)
            ->withFieldDescription(
                'task',
                'The task this job will be executing.'
            )
            // Payload
            ->withFieldType('payload', Boa::string())
            ->withFieldDefault('payload', '{}')
            ->withFieldLabel('payload', 'Payload')
            ->withFieldRequired('payload', true)
            ->withFieldDescription(
                'payload',
                'The input data passed into the task. Consult the reference ' .
                'page for more information on supported fields for each task.'
            )
            ->withFieldAnnotation('payload', 'textarea', true)
            ->withFieldConstraints('payload', [
                new StringLengthConstraint(1, 20000),
                new ValidJsonStringConstraint(),
            ])
            // Run at
            ->withFieldType('run_at', Boa::string())
            ->withFieldLabel('run_at', 'Run at')
            ->withFieldDefault(
                'run_at',
                Carbon::now()->addMinute()->format('m/d/Y H:i:s')
            )
            ->withFieldConstraints('run_at', [
                new StringLengthConstraint(0, 255),
                new DateTimeStringConstraint(),
            ])
            ->withFieldRequired('run_at', true)
            ->withFieldDescription('run_at', 'Format: mm/dd/yyyy hh:mm:ss.')
            // Expire at
            ->withFieldType('expire_at', Boa::string())
            ->withFieldDescription('expire_at', 'Format: mm/dd/yyyy hh:mm:ss.')
            ->withFieldConstraints('expire_at', [
                new StringLengthConstraint(0, 255),
                new DateTimeStringConstraint(),
            ])
            ->withFieldLabel('expire_at', 'Expire at')
            // Retries
            ->withFieldType('retries', Boa::string())
            ->withFieldConstraints('retries', [
                Boa::numeric(),
                Boa::between(0, 10000),
            ])
            ->withFieldRequired('retries', true)
            ->withFieldDefault('retries', 0)
            ->withFieldLabel('retries', 'Retries')
            ->withFieldDescription(
                'retries',
                'Additional number of times to retry this job if it fails.'
            );
    }

    /**
     * Get the check to run (a Spec, a validation, etc).
     *
     * @return CheckableInterface
     */
    public function getCheckable()
    {
        return static::getFormSpec($this->handlerResolver);
    }
}
