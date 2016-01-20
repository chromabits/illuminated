<?php

namespace Chromabits\Illuminated\Conference;

use Chromabits\Illuminated\Conference\Entities\ConferenceContext;
use Chromabits\Illuminated\Contracts\Alerts\AlertManager;
use Chromabits\Illuminated\Http\FrontCheckableRequest;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\Route;

/**
 * Class ConferenceFrontCheckableRequest.

 *
*@author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Conference
 */
abstract class ConferenceFrontCheckableRequest extends FrontCheckableRequest
{
    /**
     * @var ConferenceContext
     */
    protected $context;

    /**
     * Construct an instance of a ConferenceFrontCheckableRequest.
     *
     * @param Request $request
     * @param Route $route
     * @param Application $application
     * @param AlertManager $alerts
     * @param Redirector $redirector
     * @param ConferenceContext $context
     */
    public function __construct(Request $request,
        Route $route,
        Application $application,
        AlertManager $alerts,
        Redirector $redirector,
        ConferenceContext $context
    ) {
        parent::__construct(
            $request,
            $route,
            $application,
            $alerts,
            $redirector
        );
        $this->context = $context;
    }

    /**
     * @return string
     */
    protected function getRedirectUrl()
    {
        return $this->context->lastUrl();
    }
}
