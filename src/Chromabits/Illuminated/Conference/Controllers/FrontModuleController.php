<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Conference\Controllers;

use Chromabits\Illuminated\Conference\Entities\ConferenceContext;
use Chromabits\Illuminated\Conference\Interfaces\DashboardInterface;
use Chromabits\Illuminated\Conference\Method;
use Chromabits\Illuminated\Conference\Module;
use Chromabits\Illuminated\Http\BaseController;
use Chromabits\Nucleus\Support\Std;
use Chromabits\Nucleus\View\Bootstrap\Card;
use Chromabits\Nucleus\View\Bootstrap\CardBlock;
use Chromabits\Nucleus\View\Bootstrap\Column;
use Chromabits\Nucleus\View\Bootstrap\Row;
use Chromabits\Nucleus\View\Common\Anchor;
use Chromabits\Nucleus\View\Common\Bold;
use Chromabits\Nucleus\View\Common\Button;
use Chromabits\Nucleus\View\Common\Code;
use Chromabits\Nucleus\View\Common\Div;
use Chromabits\Nucleus\View\Common\HeaderFive;
use Chromabits\Nucleus\View\Common\HeaderFour;
use Chromabits\Nucleus\View\Common\HeaderSix;
use Chromabits\Nucleus\View\Common\HorizontalLine;
use Chromabits\Nucleus\View\Common\Italic;
use Chromabits\Nucleus\View\Common\LineBreak;
use Chromabits\Nucleus\View\Common\ListItem;
use Chromabits\Nucleus\View\Common\Paragraph;
use Chromabits\Nucleus\View\Common\PreformattedText;
use Chromabits\Nucleus\View\Common\Small;
use Chromabits\Nucleus\View\Common\UnorderedList;
use Exception;

/**
 * Class FrontModuleController.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Conference\Controllers
 */
class FrontModuleController extends BaseController
{
    /**
     * Get the homepage.
     *
     * @param DashboardInterface $dashboard
     * @param ConferenceContext $context
     *
     * @return Div
     */
    public function getIndex(
        DashboardInterface $dashboard,
        ConferenceContext $context
    ) {
        return (new Div([], [
            new Div(['class' => 'card'], [
                new Div(['class' => 'card-header'], 'About'),
                new Div(['class' => 'card-block'], [
                    new Bold([], [
                        'Hi there! This is the Illuminated Conference ',
                        'component.',
                    ]),
                    ' Here you will find a couple of built-in modules which ',
                    'you can use to debug and understand your application. ',
                    'However, you can also add your own modules for more ',
                    'specific tools!',
                ]),
            ]),
            new Div(['class' => 'row'], [
                new Div(['class' => 'col-sm-12'], [
                    new HeaderFive(['class' => 'p-b p-t'], 'Launchpad:'),
                ]),
            ]),
            $this->renderLaunchpad($dashboard, $context),
        ]));
    }

    /**
     * Render the launch bar.
     *
     * @param DashboardInterface $dashboard
     * @param ConferenceContext $context
     *
     * @return Div
     */
    protected function renderLaunchpad(
        DashboardInterface $dashboard,
        ConferenceContext $context
    ) {
        return new Div(['class' => 'card-columns'], [
            new Div(['class' => 'card-stack'], Std::map(
                function (Module $module) use ($context) {
                    return new Div(['class' => 'card'], [
                        new Div(['class' => 'card-block'], [
                            new Anchor(
                                [
                                    'href' => $context->module(
                                        $module->getName()
                                    ),
                                ],
                                [
                                    new HeaderFour(
                                        ['class' => 'card-title'],
                                        [
                                            $module->getLabel(),
                                            new Italic([
                                                'class' => 'pull-right fa ' .
                                                    $module->getIcon()
                                            ]),
                                        ]
                                    ),
                                ]
                            ),
                            new Paragraph(
                                ['class' => 'card-text'],
                                $module->getDescription()
                            ),
                            new Paragraph(
                                ['class' => 'card-text'],
                                new Small(['class' => 'text-muted'], [
                                    $module->getName(),
                                ])
                            ),
                        ]),
                    ]);
                },
                $dashboard->getModules()
            )),
        ]);
    }

    /**
     * Get information about loaded modules.
     *
     * @param DashboardInterface $dashboard
     *
     * @return Div
     */
    public function getModules(DashboardInterface $dashboard)
    {
        return (new Div([], [
            new Div(['class' => 'card'], [
                new Div(['class' => 'card-header'], 'Available modules'),
                new Div(
                    ['class' => 'card-block'],
                    Std::map(function (Module $module) {
                        return new Div([], [
                            new HeaderFive([], $module->getName()),
                            new Paragraph([], [
                                new Bold([], 'Class Name: '),
                                new Code([], get_class($module)),
                                new LineBreak([]),
                                new Bold([], 'Methods: '),
                            ]),
                            new UnorderedList([], Std::map(
                                function (Method $method) {
                                    return new ListItem([], vsprintf(
                                        '%s (%s) -> %s@%s', [
                                            $method->getLabel(),
                                            $method->getName(),
                                            $method->getControllerClassName(),
                                            $method->getControllerMethodName(),
                                        ])
                                    );
                                },
                                $module->getMethods()
                            )),
                        ]);
                    }, $dashboard->getModules())
                ),
            ]),
        ]));
    }

    /**
     * Get problematic modules.
     *
     * @param DashboardInterface $dashboard
     *
     * @return Div
     */
    public function getIssues(DashboardInterface $dashboard)
    {
        $exceptions = Std::map(function (Exception $exception, $moduleName) {
            return new Div([], [
                new Div(['class' => 'card card-inverted'], [
                    new CardBlock([], [
                        new HeaderSix(['class' => 'text-muted'], $moduleName),
                        new Bold([], get_class($exception) . ': '),
                        $exception->getMessage(),
                        new Div(
                            ['class' => 'collapse p-t', 'id' => 'stack'],
                            new PreformattedText(
                                ['class' => 'pre-scrollable'],
                                $exception->getTraceAsString()
                            )
                        ),
                    ]),
                    new Div(['class' => 'card-footer text-muted'], [
                        new Row([], [
                            new Column(['medium' => 6], [
                                basename($exception->getFile()) . ':'
                                . $exception->getLine(),
                            ]),
                            new Column(
                                ['medium' => 6, 'class' => 'text-xs-right'],
                                new Button(
                                    [
                                        'href' => '#',
                                        'class' => [
                                            'btn',
                                            'btn-sm',
                                            'btn-primary-outline',
                                        ],
                                        'data-toggle' => 'collapse',
                                        'data-target' => '#stack',
                                        'aria-expanded' => 'false',
                                        'aria-controls' => '#stack',
                                    ],
                                    'Toggle stacktrace'
                                )
                            ),
                        ]),

                    ]),
                ]),
            ]);
        }, $dashboard->getFailedModules());

        return (new Div([], [
            new Div(['class' => 'card'], [
                new Div(['class' => 'card-header'], 'Module issues'),
                new Div(
                    ['class' => 'card-block'],
                    [
                        'Below you will find a list of all the modules that ',
                        'failed to load. If one or more failed to load, it is ',
                        'not necessarily a bad thing. If you do not intend to ',
                        'use the component covered by the module, you may ',
                        'safely ignore it.',
                    ]
                ),
            ]),
            new HorizontalLine([]),
            new Div([], Std::firstBias(
                count($dashboard->getFailedModules()) > 0,
                $exceptions,
                function () {
                    return new Card(
                        ['class' => 'card card-block text-center'],
                        ['All modules seem fine!']
                    );
                }
            )),
        ]));
    }
}
