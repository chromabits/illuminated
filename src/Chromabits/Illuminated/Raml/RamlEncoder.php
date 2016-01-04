<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Raml;

use Chromabits\Illuminated\Foundation\Interfaces\ApplicationManifestInterface;
use Chromabits\Illuminated\Http\ApiCheckableRequest;
use Chromabits\Illuminated\Http\Factories\ResourceFactory;
use Chromabits\Illuminated\Http\Interfaces\AnnotatedControllerInterface;
use Chromabits\Illuminated\Http\ResourceReflector;
use Chromabits\Illuminated\Json\SpecSchemaEncoder;
use Chromabits\Illuminated\Raml\Interfaces\RamlEncoderInterface;
use Chromabits\Nucleus\Foundation\BaseObject;
use Chromabits\Nucleus\Http\Enums\HttpMethods;
use Chromabits\Nucleus\Meditation\Constraints\PrimitiveTypeConstraint;
use Chromabits\Nucleus\Meditation\Primitives\ScalarTypes;
use Chromabits\Nucleus\Meditation\Spec;
use Chromabits\Nucleus\Support\Arr;
use Chromabits\Nucleus\Support\Std;
use Chromabits\Nucleus\Validation\Validator;
use Illuminate\Contracts\Container\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RamlEncoder.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Raml
 */
class RamlEncoder extends BaseObject implements RamlEncoderInterface
{
    /**
     * @var Container
     */
    protected $app;

    /**
     * Construct an instance of a RamlEncoder.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        parent::__construct();

        $this->app = $container;
    }

    /**
     * Generate a raml file describing the application.
     *
     * @param ApplicationManifestInterface $manifest
     * @param RamlEncoderOptions $options
     *
     * @return string
     */
    public function encode(
        ApplicationManifestInterface $manifest,
        RamlEncoderOptions $options = null
    ) {
        $options = Std::coalesceThunk(
            $options,
            function () use ($manifest) {
                if ($manifest->hasProperty('ramlEncoderOptions')) {
                    return $manifest->getProperty('ramlEncoderOptions');
                }

                return RamlEncoderOptions::defaultOptions();
            }
        );

        $root = [
            'title' => $manifest->getName(),
            'version' => $manifest->getCurrentVersion(),
            'mediaType' => 'application/json',
            'baseUri' => $manifest->getBaseUri(),
        ];

        if (count($manifest->getProse())) {
            foreach ($manifest->getProse() as $title => $content) {
                $root['documentation'][] = [
                    'title' => $title,
                    'content' => $content,
                ];
            }
        }

        foreach ($manifest->getApiResources() as $resource) {
            $path = $resource->getPrefix();

            if ($path == '') {
                $path = '/';
            }

            if (Arr::has($root, $path)) {
                $root[$path] = Arr::merge(
                    $root[$path],
                    $this->encodeResource($resource, $options)
                );

                continue;
            }

            $root[$path] = $this->encodeResource($resource, $options);
        }

        if ($options !== null) {
            $root['securitySchemes'] = Std::map(
                function ($scheme) {
                    $result = [];

                    foreach ($scheme as $key => $property) {
                        $result[$key] = $property->toArray();
                    }

                    return $result;
                },
                $options->getSecuritySchemes()
            );
        }

        $yaml = yaml_emit(RamlUtils::filterEmptyValues($root));

        return str_replace("---\n", "#%RAML 0.8\n", $yaml);
    }

    protected function encodeResource(
        ResourceFactory $resource,
        RamlEncoderOptions $options
    ) {
        $controller = $this->app->make($resource->getController());

        $ramlResource = [
            'displayName' => $resource->getName(),
            'description' => $resource->getDescription(),
        ];

        foreach ($resource->getMethods() as $method) {
            $ramlAction = [
                'securedBy' => $this->middlewareToSecuritySchemes(
                    $options,
                    $resource->getMiddleware()
                ),
            ];

            if ($controller instanceof AnnotatedControllerInterface) {
                $ramlAction['description'] = $controller->getMethodDescription(
                    $method->getMethod()
                );
                $ramlAction['body'] = $this->requestsToBody(
                    $controller->getMethodExampleRequests(
                        $method->getMethod()
                    )
                )->toArray();
                $ramlAction['responses'] = $this->responsesToGroup(
                    $controller->getMethodExampleResponses(
                        $method->getMethod()
                    )
                )->toArray();
            } else {
                $ramlAction['description']
                    = 'This method does not provide a description';
            }

            $reflector = new ResourceReflector($this->app);
            $request = $reflector->getMethodRequest($resource, $method);
            $uriParameters = [];
            $queryParameters = [];

            if ($request instanceof ApiCheckableRequest) {
                $spec = $request->getCheckable();

                if ($spec instanceof Validator) {
                    $spec = $spec->getSpec();
                }

                if ($spec instanceof Spec) {
                    if ($method->getVerb() == HttpMethods::POST
                        || $method->getVerb() == HttpMethods::PUT
                        || $method->getVerb() == HttpMethods::DELETE
                    ) {
                        $this->addPostSchema(
                            $ramlResource,
                            $ramlAction,
                            $uriParameters,
                            $resource,
                            $method,
                            $spec,
                            $reflector
                        );
                    } else {
                        $parameters = $reflector->getMethodParameters(
                            $resource,
                            $method
                        );

                        $fields = array_unique(
                            array_merge(
                                array_keys($spec->getConstraints()),
                                array_keys($spec->getDefaults()),
                                $spec->getRequired()
                            )
                        );

                        foreach ($fields as $field) {
                            if (in_array($field, $parameters)) {
                                $uriParameters[$field]
                                    = $this->specFieldToParameter(
                                    $spec,
                                    $field
                                );

                                continue;
                            }

                            $queryParameters[$field]
                                = $this->specFieldToParameter(
                                $spec,
                                $field
                            );
                        }
                    }
                }
            }

            if (!Arr::has($ramlResource, $method->getPath())) {
                $ramlResource[$method->getPath()] = [];
            }

            $ramlResource[$method->getPath()]['uriParameters'] =
                $uriParameters;

            $ramlAction['queryParameters'] = $queryParameters;

            $verb = strtolower($method->getVerb());
            $ramlResource[$method->getPath()][$verb]
                = RamlUtils::filterEmptyValues($ramlAction);

            $ramlResource[$method->getPath()] = RamlUtils::filterEmptyValues(
                $ramlResource[$method->getPath()]
            );
        }

        return RamlUtils::filterEmptyValues($ramlResource);
    }

    /**
     * @param $ramlResource
     * @param $ramlAction
     * @param $uriParameters
     * @param $resource
     * @param $method
     * @param Spec $spec
     * @param $reflector
     */
    protected function addPostSchema(
        &$ramlResource,
        &$ramlAction,
        &$uriParameters,
        $resource,
        $method,
        Spec $spec,
        $reflector
    ) {
        if (Arr::has($ramlAction, 'body')) {
            $ramlAction['body'] = [];
        }

        $parameters = $reflector->getMethodParameters(
            $resource,
            $method
        );

        $fields = array_unique(
            array_merge(
                array_keys($spec->getConstraints()),
                array_keys($spec->getDefaults()),
                $spec->getRequired()
            )
        );

        $specFields = [];

        foreach ($fields as $field) {
            if (in_array($field, $parameters)) {
                $uriParameters[$field]
                    = $this->specFieldToParameter(
                    $spec,
                    $field
                );

                continue;
            }

            $specFields[] = $field;
        }

        $filteredSpec = new Spec(
            Arr::only($spec->getConstraints(), $specFields),
            Arr::only($spec->getDefaults(), $specFields),
            Std::filter(function ($element) use ($specFields) {
                return in_array($element, $specFields);
            }, $spec->getRequired())
        );

        $ramlAction['body']['schema'] = (new SpecSchemaEncoder())
            ->encode($filteredSpec);
    }

    /**
     * Translate middleware to Raml security schemes.
     *
     * @param RamlEncoderOptions $options
     * @param array $middleware
     *
     * @return array
     */
    protected function middlewareToSecuritySchemes(
        RamlEncoderOptions $options,
        $middleware
    ) {
        $mapping = $options->getMiddlewareToSchemeMapping();

        return array_unique(
            Std::foldl(
                function ($acc, $current) use ($mapping) {
                    if (Arr::has($mapping, $current)) {
                        return $acc + [$mapping[$current]];
                    }

                    return $acc;
                },
                [],
                $middleware
            )
        );
    }

    /**
     * Get a RAML parameter definition from a Spec field.
     *
     * @param Spec $spec
     * @param string $field
     *
     * @return array
     */
    protected function specFieldToParameter(Spec $spec, $field)
    {
        $constraints = $spec->getConstraints();
        $defaults = $spec->getDefaults();
        $required = $spec->getRequired();

        $parameter = [];

        if (Arr::has($constraints, $field)) {
            $input = $constraints[$field];

            if (is_array($input) || $input instanceof Spec) {
                if ($input instanceof Spec) {
                    $input = [$input];
                }

                foreach ($input as $constraint) {
                    if ($constraint instanceof PrimitiveTypeConstraint) {
                        switch ($constraint->toString()) {
                            case ScalarTypes::SCALAR_STRING:
                                $parameter['type'] = 'string';
                                break;
                            case ScalarTypes::SCALAR_FLOAT:
                            case ScalarTypes::SCALAR_INTEGER:
                                $parameter['type'] = 'number';
                                break;
                            case ScalarTypes::SCALAR_BOOLEAN:
                                $parameter['type'] = 'boolean';
                                break;
                        }
                    }
                }
            }
        }

        if (Arr::has($defaults, $field)) {
            $parameter['default'] = $defaults[$field];
        }

        $parameter['required'] = in_array($field, $required);

        return $parameter;
    }

    /**
     * Turn response examples to a RAML response group.
     *
     * @param Response[] $responses
     *
     * @return RamlResponseGroup
     */
    protected function responsesToGroup($responses)
    {
        return Std::foldl(
            function (RamlResponseGroup $group, Response $response) {
                if ($response->headers->get('content-type')
                    == 'application/json'
                ) {
                    $response = $this->prettyPrintJsonResponse($response);
                }

                return $group->addResponse(
                    $response->getStatusCode(),
                    (new RamlResponse())->setBody(
                        (new RamlMessageBody())
                            ->addType(
                                $response->headers->get('content-type'),
                                (new RamlBody())->setExample(
                                    $response->getContent()
                                )
                            )
                    )
                );
            },
            new RamlResponseGroup(),
            $responses
        );
    }

    /**
     * Turn request examples to a RAML message body.
     *
     * @param Request[] $requests
     *
     * @return RamlMessageBody
     */
    protected function requestsToBody($requests)
    {
        return Std::foldl(
            function (RamlMessageBody $messageBody, Request $request) {
                return $messageBody->addType(
                    $request->headers->get('content-type'),
                    (new RamlBody())->setExample(
                        $request->getContent()
                    )
                );
            },
            new RamlMessageBody(),
            $requests
        );
    }

    /**
     * Pretty print a JSON response so that it easier to read on API consoles.
     *
     * @param Response $response
     *
     * @return Response
     */
    protected function prettyPrintJsonResponse(Response $response)
    {
        $new = clone $response;

        $new->setContent(
            json_encode(
                json_decode($response->getContent()),
                JSON_PRETTY_PRINT
            )
        );

        return $new;
    }
}
