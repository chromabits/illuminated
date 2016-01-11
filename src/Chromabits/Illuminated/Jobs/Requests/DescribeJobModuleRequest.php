<?php

namespace Chromabits\Illuminated\Jobs\Requests;

use Chromabits\Illuminated\Contracts\Alerts\AlertManager;
use Chromabits\Illuminated\Http\FrontCheckableRequest;
use Chromabits\Illuminated\Jobs\Interfaces\JobRepositoryInterface;
use Chromabits\Nucleus\Meditation\Boa;
use Chromabits\Nucleus\Meditation\Interfaces\CheckableInterface;
use Chromabits\Nucleus\Meditation\TypedSpec;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\Route;

/**
 * Class DescribeJobModuleRequest
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Jobs\Requests
 */
class DescribeJobModuleRequest extends FrontCheckableRequest
{
    /**
     * @var JobRepositoryInterface
     */
    protected $jobRepository;

    /**
     * Construct an instance of a DescribeJobModuleRequest.
     *
     * @param Request $request
     * @param Route $route
     * @param Application $application
     * @param AlertManager $alerts
     * @param Redirector $redirector
     * @param JobRepositoryInterface $jobRepository
     */
    public function __construct(
        Request $request,
        Route $route,
        Application $application,
        AlertManager $alerts,
        Redirector $redirector,
        JobRepositoryInterface $jobRepository
    ) {
        parent::__construct(
            $request,
            $route,
            $application,
            $alerts,
            $redirector
        );

        $this->jobRepository = $jobRepository;
    }

    /**
     * Get the check to run (a Spec, a validation, etc).
     *
     * @return CheckableInterface
     */
    public function getCheckable()
    {
        return (new TypedSpec())
            ->withFieldType('id', Boa::string())
            ->withFieldConstraints('id', [
                $this->jobRepository->makeExistsConstraint()
            ]);
    }
}
