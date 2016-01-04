<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Foundation\Controllers;

use Chromabits\Illuminated\Conference\Entities\ConferenceContext;
use Chromabits\Illuminated\Foundation\ApplicationManifest;
use Chromabits\Illuminated\Foundation\Interfaces\ApplicationManifestInterface;
use Chromabits\Illuminated\Http\ApiCheckableRequest;
use Chromabits\Illuminated\Http\BaseController;
use Chromabits\Illuminated\Http\Entities\ResourceMethod;
use Chromabits\Illuminated\Http\Factories\ResourceFactory;
use Chromabits\Illuminated\Http\ResourceReflector;
use Chromabits\Nucleus\Meditation\Constraints\AbstractConstraint;
use Chromabits\Nucleus\Meditation\Spec;
use Chromabits\Nucleus\Support\Html;
use Chromabits\Nucleus\Support\Std;
use Chromabits\Nucleus\Validation\Validator;
use Chromabits\Nucleus\View\Bootstrap\Card;
use Chromabits\Nucleus\View\Bootstrap\CardBlock;
use Chromabits\Nucleus\View\Bootstrap\CardHeader;
use Chromabits\Nucleus\View\Common\Anchor;
use Chromabits\Nucleus\View\Common\Bold;
use Chromabits\Nucleus\View\Common\Div;
use Chromabits\Nucleus\View\Common\HeaderOne;
use Chromabits\Nucleus\View\Common\HeaderThree;
use Chromabits\Nucleus\View\Common\Italic;
use Chromabits\Nucleus\View\Common\Paragraph;
use Chromabits\Nucleus\View\Common\Table;
use Chromabits\Nucleus\View\Common\TableBody;
use Chromabits\Nucleus\View\Common\TableCell;
use Chromabits\Nucleus\View\Common\TableHeader;
use Chromabits\Nucleus\View\Common\TableHeaderCell;
use Chromabits\Nucleus\View\Common\TableRow;
use Chromabits\Nucleus\View\Node;
use Exception;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use League\CommonMark\CommonMarkConverter;

/**
 * Class ApplicationController.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Foundation\Controllers
 */
class ApplicationController extends BaseController
{
    /**
     * @var ApplicationManifest
     */
    protected $manifest;

    /**
     * @var Container
     */
    protected $container;

    /**
     * Construct an instance of a ApplicationController.
     *
     * @param ApplicationManifestInterface $manifest
     * @param Container $container
     */
    public function __construct(
        ApplicationManifestInterface $manifest,
        Container $container
    ) {
        $this->manifest = $manifest;
        $this->container = $container;
    }

    /**
     * Show basic application information.
     *
     * @param ConferenceContext $context
     *
     * @return Div
     */
    public function getIndex(ConferenceContext $context)
    {
        return new Div([], [
            new Card([], [
                new CardHeader([], 'Application Information'),
                new CardBlock([], [
                    new Paragraph([], [
                        new HeaderOne([], $this->manifest->getName()),
                    ]),
                    new Paragraph([], [
                        $this->manifest->getDescription(),
                    ]),
                ]),
            ]),
            $this->renderResources($context),
        ]);
    }

    /**
     * @return Div
     */
    public function getProse()
    {
        return new Div([],
            Std::map(function ($contents, $title) {
                return new Card([], [
                    new CardHeader([], $title),
                    new CardBlock([], [
                        Html::safe((new CommonMarkConverter())->convertToHtml(
                            $contents
                        )),
                    ]),
                ]);
            }, $this->manifest->getProse())
        );
    }

    /**
     * @param ConferenceContext $context
     *
     * @return Div
     */
    protected function renderResources(ConferenceContext $context)
    {
        return new Div([], Std::map(
            function (ResourceFactory $factory, $key) use ($context) {
                return $this->renderResource($context, $factory, false, $key);
            },
            $this->manifest->getResources())
        );
    }

    /**
     * @param ConferenceContext $context
     * @param ResourceFactory $factory
     * @param bool $reflect
     * @param int $id
     *
     * @return Div
     */
    protected function renderResource(
        ConferenceContext $context,
        ResourceFactory $factory,
        $reflect = false,
        $id = 0
    ) {
        return new Div([], [
            new Card([], [
                new CardHeader([], [
                    Std::coalesce($factory->getPrefix(), '/'),
                    ' ',
                    new Italic(
                        [
                            'class' => [
                                'fa',
                                'fa-arrow-circle-right ',
                            ],
                        ]
                    ),
                    ' ',
                    new Anchor(
                        ['href' => $context->method(
                            'illuminated.conference.application',
                            'single',
                            ['resource' => $id]
                        )],
                        new Bold([], $factory->getController())
                    ),
                ]),
                new CardBlock([], [
                    new Paragraph([], [
                        new Bold([], 'Middleware: '),
                        implode(', ', $factory->getMiddleware()),
                    ]),
                    new Div([], Std::map(
                        function (ResourceMethod $method) use (
                            $factory,
                            $reflect
                        ) {
                            return $this->renderRoute(
                                $factory,
                                $method,
                                $reflect
                            );
                        },
                        $factory->getMethods())
                    ),
                ]),
            ]),
        ]);
    }

    /**
     * @param ResourceFactory $factory
     * @param ResourceMethod $method
     *
     * @return Div|string
     */
    protected function reflectOnRequest(
        ResourceFactory $factory,
        ResourceMethod $method
    ) {
        try {
            $reflector = new ResourceReflector($this->container);

            $request = $reflector->getRequest(
                $reflector->getMethodArgumentTypes($factory, $method)
            );

            if ($request instanceof ApiCheckableRequest) {
                $spec = $request->getCheckable();

                if ($spec instanceof Validator) {
                    $spec = $spec->getSpec();
                }

                if (!$spec instanceof Spec) {
                    return 'Only Specs are supported at the moment.';
                }

                return new Div([], [
                    new Div([], [
                        new Bold([], 'Required: '),
                        implode(', ', $spec->getRequired()),
                    ]),
                    new Div([], [
                        new Table(
                            ['class' => 'table'], [
                                new TableHeader([], [
                                    new TableRow([], [
                                        new TableHeaderCell([], 'Field'),
                                        new TableHeaderCell([], 'Constraints'),
                                        new TableHeaderCell([], 'Default'),
                                    ]),
                                ]),
                                new TableBody([], Std::map(
                                    function ($value, $key) use ($spec) {
                                        return $this->renderConstraint(
                                            $spec,
                                            $key
                                        );
                                    },
                                    $spec->getConstraints())
                                ),
                            ]
                        ),
                    ]),
                ]);
            } else {
                return 'Handler cannot be reflected upon.';
            }
        } catch (Exception $e) {
            return 'Unable to resolve handler';
        }
    }

    /**
     * @param Spec $spec
     * @param string $field
     *
     * @return TableRow
     */
    protected function renderConstraint(Spec $spec, $field)
    {
        $constraints = $spec->getConstraints();
        $defaults = $spec->getDefaults();
        $default = '';

        if (array_key_exists($field, $defaults)) {
            $default = $defaults[$field];
        }

        $constraint = $constraints[$field];

        if ($constraint instanceof AbstractConstraint) {
            return new TableRow([], [
                new TableCell([], $field),
                new TableCell([], $constraint->toString()),
                new TableCell([], (string) $default),
            ]);
        }

        return new TableRow([], [
            new TableCell([], $field),
            new TableCell([], Std::map(
                function (AbstractConstraint $constraint) {
                    return $constraint->toString() . ', ';
                },
                $constraint
            )),
            new TableCell([], (string) $default),
        ]);
    }

    /**
     * @param ResourceFactory $factory
     * @param ResourceMethod $method
     * @param bool $reflect
     *
     * @return Div
     */
    protected function renderRoute(
        ResourceFactory $factory,
        ResourceMethod $method,
        $reflect = false
    ) {
        if ($reflect) {
            $reflection = $this->reflectOnRequest($factory, $method);

            return new Div(['style' => 'padding-top: 20px;'], [
                new HeaderThree([], [
                    new Node('code', [], strtoupper($method->getVerb())),
                    ' ',
                    new Bold([], $method->getPath()),
                    ' ',
                    $method->getMethod(),
                ]),
                new Div([], $reflection),
            ]);
        }

        return new Div(['style' => 'padding-top: 10px;'], [
            new Node('code', [], strtoupper($method->getVerb())),
            ' ',
            new Bold([], $method->getPath()),
            ' ',
            $method->getMethod(),
        ]);
    }

    /**
     * @param Request $request
     * @param ConferenceContext $context
     *
     * @return Div
     */
    public function getSingle(Request $request, ConferenceContext $context)
    {
        if (!$request->query->has('resource')) {
            return new Div([], [
                new Card([], [
                    new CardHeader([], 'Error'),
                    new CardBlock([], [
                        'Please provide valid resource.',
                    ]),
                ]),
            ]);
        }

        $index = $request->query->get('resource');

        return new Div([], [
            $this->renderResource(
                $context,
                $this->manifest->getResources()[$index],
                true,
                $index
            ),
        ]);
    }
}
