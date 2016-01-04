<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Http\Traits;

use Chromabits\Nucleus\Support\Arr;
use Chromabits\Nucleus\Support\Str;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait AnnotatedControllerTrait.
 *
 * Provides a basic implementation of the methods of AnnotatedControllerTrait.
 *
 * This class expects the following properties in the controller:
 * - methodDescriptions: A map of a method name and a description of what the
 *  method does.
 * - methodNames: A map of a method name and a human-readable name for the
 *  method.
 *
 * You can also define properties with the following patterns:
 * - {name}Name: A human-readable name of a method.
 * - {name}Description: A description of what a method does.
 *
 * For example requests and responses, you can define methods with the
 * following patterns:
 * - get{name}ExampleRequests
 * - get{name}ExampleResponses
 * - get{name}Headers
 *
 * NOTE: All properties and methods should be in camelCase.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Http\Traits
 */
trait AnnotatedControllerTrait
{
    /**
     * Get a text description for a method.
     *
     * @param string $methodName
     *
     * @return string
     */
    public function getMethodDescription($methodName)
    {
        $propertyName = vsprintf('%sDescription', [$methodName]);

        if (property_exists($this, $propertyName)) {
            return $this->$propertyName;
        }

        if (property_exists($this, 'methodDescriptions')
            && Arr::has($this->methodDescriptions, $methodName)
        ) {
            return $this->methodDescriptions[$methodName];
        }

        return 'This method does not provide a description';
    }

    /**
     * Get the display name of a method.
     *
     * @param string $methodName
     *
     * @return string
     */
    public function getMethodName($methodName)
    {
        $propertyName = vsprintf('%sName', [$methodName]);

        if (property_exists($this, $propertyName)) {
            return $this->$propertyName;
        }

        if (property_exists($this, 'methodNames')
            && Arr::has($this->methodNames, $methodName)
        ) {
            return $this->methodNames[$methodName];
        }

        return 'Unknown';
    }

    /**
     * Get the required headers for a method.
     *
     * @param string $methodName
     *
     * @return array
     */
    public function getMethodHeaders($methodName)
    {
        $getterName = vsprintf('get%sHeaders', [
            Str::studly($methodName),
        ]);

        if (method_exists($this, $getterName)) {
            return $this->$getterName();
        }

        return [];
    }

    /**
     * Get a list of example requests for a specific method.
     *
     * @param string $methodName
     *
     * @return Request[]
     */
    public function getMethodExampleRequests($methodName)
    {
        $getterName = vsprintf('get%sExampleRequests', [
            Str::studly($methodName),
        ]);

        if (method_exists($this, $getterName)) {
            return $this->$getterName();
        }

        return [];
    }

    /**
     * Get a list of example responses for a specific method.
     *
     * @param string $methodName
     *
     * @return Response[]
     */
    public function getMethodExampleResponses($methodName)
    {
        $getterName = vsprintf('get%sExampleResponses', [
            Str::studly($methodName),
        ]);

        if (method_exists($this, $getterName)) {
            return $this->$getterName();
        }

        return [];
    }

    /**
     * A helper method for creating mock requests with just a body.
     *
     * @param mixed $contents
     *
     * @return Request
     */
    protected function simpleRequest($mimeType, $contents)
    {
        $request = Request::create('/', 'GET', [], [], [], [], $contents);

        $request->headers->set('content-type', $mimeType);

        return $request;
    }

    /**
     * A helper method for creating mock JSON requests with just a body.
     *
     * @param mixed $contents
     *
     * @return Request
     */
    protected function simpleJsonRequest($contents)
    {
        return $this->simpleRequest(
            'application/json',
            json_encode($contents, JSON_PRETTY_PRINT)
        );
    }
}
