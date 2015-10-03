<?php

namespace Chromabits\Illuminated\Http;

use Chromabits\Illuminated\Http\Interfaces\ApiResponseFactoryInterface;
use Chromabits\Nucleus\Meditation\Interfaces\CheckableInterface;
use Chromabits\Nucleus\Meditation\Interfaces\CheckResultInterface;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

/**
 * Class ApiCheckableRequest
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Http
 */
abstract class ApiCheckableRequest extends CheckableRequest
{
    /**
     * @var ApiResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * Construct an instance of a ApiCheckableRequest.
     *
     * @param Request $request
     * @param Route $route
     * @param ApiResponseFactoryInterface $responseFactory
     */
    public function __construct(
        Request $request,
        Route $route,
        ApiResponseFactoryInterface $responseFactory
    ) {
        parent::__construct($request, $route);

        $this->responseFactory = $responseFactory;
    }

    /**
     * Handle the case where check does not pass.
     *
     * Here you can throw an exception, flash messages, etc to the user.
     *
     * @param CheckableInterface $check
     * @param CheckResultInterface $result
     */
    public function handleFailure(
        CheckableInterface $check,
        CheckResultInterface $result
    ) {
        throw new HttpResponseException(
            $this->responseFactory->fromCheckable($result)->toResponse()
        );
    }
}