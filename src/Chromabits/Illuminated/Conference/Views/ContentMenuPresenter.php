<?php

namespace Chromabits\Illuminated\Conference\Views;

use Chromabits\Illuminated\Conference\Entities\ConferenceContext;
use Chromabits\Nucleus\Data\ArrayList;
use Chromabits\Nucleus\Data\Interfaces\ListInterface;
use Chromabits\Nucleus\View\BaseHtmlRenderable;
use Chromabits\Nucleus\View\Bootstrap\Column;
use Chromabits\Nucleus\View\Bootstrap\Row;
use Chromabits\Nucleus\View\Common\Anchor;
use Chromabits\Nucleus\View\Composite\AwesomeIcon;

/**
 * Class ContentMenuPresenter
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Conference\Views
 */
class ContentMenuPresenter extends BaseHtmlRenderable
{
    /**
     * @var ListInterface
     */
    protected $content;

    /**
     * @var ListInterface
     */
    protected $actions;

    /**
     * @var ConferenceContext
     */
    protected $context;

    /**
     * Construct an instance of a ContentMenuPresenter.
     *
     * @param ConferenceContext $context
     */
    public function __construct(ConferenceContext $context)
    {
        parent::__construct();

        $this->context = $context;
        $this->actions = ArrayList::zero();
    }

    /**
     * Get this presenter with an added module action.
     *
     * @param string $icon
     * @param string $label
     * @param string $moduleName
     * @param string|null $moduleMethod
     * @param array $parameters
     *
     * @return ContentMenuPresenter
     */
    public function withContextAction(
        $icon,
        $label,
        $moduleName,
        $moduleMethod = null,
        $parameters = []
    ) {
        if ($moduleMethod === null) {
            return $this->withAction($icon,
                $label,
                $this->context->module(
                    $moduleName,
                    $parameters
                ));
        }

        return $this->withAction($icon,
            $label,
            $this->context->method(
                $moduleName,
                $moduleMethod,
                $parameters
            ));
    }

    /**
     * Get this presenter with an added action.
     *
     * @param string $icon
     * @param string $label
     * @param string $url
     *
     * @return ContentMenuPresenter
     */
    public function withAction($icon, $label, $url = null)
    {
        $copy = clone $this;

        $copy->actions = $this->actions->append(ArrayList::of([
            [
                'icon' => $icon,
                'label' => $label,
                'url' => $url,
            ]
        ]));

        return $copy;
    }

    /**
     * Set the content.
     *
     * @param mixed $content
     *
     * @return ContentMenuPresenter
     */
    public function withContent($content)
    {
        $copy = clone $this;

        $copy->content = $content;

        return $copy;
    }

    /**
     * Render the object into a string.
     *
     * @return mixed
     */
    public function render()
    {
        return new Row([], [
            new Column(['class' => 'col col-sm-8'], $this->content),
            new Column(
                ['class' => 'col col-sm-4 btn-group-vertical'],
                $this->actions->map(function ($action) {
                    if ($action['url'] === null) {
                        return new Anchor(
                            [
                                'class' => 'btn btn-secondary disabled',
                            ],
                            [
                                (new AwesomeIcon($action['icon']))
                                    ->setFixedWidth(),
                                ' ',
                                $action['label']
                            ]
                        );
                    }

                    return new Anchor(
                        [
                            'href' => $action['url'],
                            'class' => 'btn btn-secondary',
                        ],
                        [
                            (new AwesomeIcon($action['icon']))
                                ->setFixedWidth(),
                            ' ',
                            $action['label']
                        ]
                    );
                })
            ),
        ]);
    }
}
