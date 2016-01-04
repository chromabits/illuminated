<?php

namespace Chromabits\Illuminated\Database\Views;

use Chromabits\Illuminated\Database\Interfaces\StructuredStatusInterface;
use Chromabits\Illuminated\Database\Migrations\StatusReport;
use Chromabits\Nucleus\Foundation\BaseObject;
use Chromabits\Nucleus\View\Bootstrap\Card;
use Chromabits\Nucleus\View\Bootstrap\CardBlock;
use Chromabits\Nucleus\View\Bootstrap\Column;
use Chromabits\Nucleus\View\Bootstrap\Row;
use Chromabits\Nucleus\View\Common\HeaderOne;
use Chromabits\Nucleus\View\Common\Paragraph;
use Chromabits\Nucleus\View\Common\Small;
use Chromabits\Nucleus\View\Interfaces\RenderableInterface;

/**
 * Class ConferenceStructuredStatisticsPresenter.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Database\Views
 */
class ConferenceStructuredStatisticsPresenter extends BaseObject implements
    RenderableInterface
{
    /**
     * @var StructuredStatusInterface
     */
    protected $report;

    /**
     * Construct an instance of a ConferenceStructuredStatisticsPresenter.
     *
     * @param StatusReport $report
     */
    public function __construct(StatusReport $report)
    {
        parent::__construct();

        $this->report = $report;
    }

    /**
     * Render the object into a string.
     *
     * @return mixed
     */
    public function render()
    {
        return new Row([], [
            new Column(['medium' => 6, 'class' => 'text-xs-center'], [
                new Card([], new CardBlock([], [
                    new HeaderOne(
                        ['class' => 'display-3'],
                        vsprintf('%d', [
                            count($this->report->getRan())
                        ])
                    ),
                    new Paragraph([],
                        new Small(['class' => 'text-uppercase text-muted'], [
                            'Migrations ran so far'
                        ])
                    )
                ])),
            ]),
            new Column(['medium' => 6, 'class' => 'text-xs-center'], [
                new Card([], new CardBlock([], [
                    new HeaderOne(
                        ['class' => 'display-3'],
                        vsprintf('%d', [
                            count($this->report->getIdle())
                        ])
                    ),
                    new Paragraph([],
                        new Small(['class' => 'text-uppercase text-muted'], [
                            'Pending to be ran'
                        ])
                    )
                ]))
            ]),
        ]);
    }
}