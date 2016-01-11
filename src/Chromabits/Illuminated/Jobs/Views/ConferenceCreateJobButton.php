<?php

namespace Chromabits\Illuminated\Jobs\Views;

use Chromabits\Illuminated\Conference\Entities\ConferenceContext;
use Chromabits\Illuminated\Conference\Views\ContextAnchor;
use Chromabits\Illuminated\Jobs\Modules\JobsModule;
use Chromabits\Nucleus\View\BaseHtmlRenderable;
use Chromabits\Nucleus\View\Bootstrap\Column;
use Chromabits\Nucleus\View\Composite\AwesomeIcon;

/**
 * Class ConferenceCreateJobButton
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Jobs\Views
 */
class ConferenceCreateJobButton extends BaseHtmlRenderable
{
    /**
     * @var ConferenceContext
     */
    protected $context;

    /**
     * Construct an instance of a ConferenceCreateJobButton.
     *
     * @param ConferenceContext $context
     */
    public function __construct(ConferenceContext $context)
    {
        parent::__construct();

        $this->context = $context;
    }

    /**
     * Render the object into a string.
     *
     * @return mixed
     */
    public function render()
    {
        return new Column(['medium' => 6, 'class' => 'text-xs-right'], [
            (new ContextAnchor($this->context))
                ->withAttributes([
                    'class' => 'btn btn-sm btn-primary-outline'
                ])
                ->withModule(JobsModule::NAME)
                ->withMethod('create')
                ->withContent([
                    new AwesomeIcon('plus'),
                    ' ',
                    'Create new job'
                ]),
        ]);
    }
}
