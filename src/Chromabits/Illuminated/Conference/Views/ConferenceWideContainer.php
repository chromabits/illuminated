<?php

namespace Chromabits\Illuminated\Conference\Views;

use Chromabits\Nucleus\View\BaseHtmlRenderable;
use Chromabits\Nucleus\View\Interfaces\RenderableInterface;

/**
 * Class ConferenceWideContainer
 *
 * A simple wrapper that Conference uses to determine if the content returned
 * by a module method wants to hide the sidebar and take over the entire width
 * of the page.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Conference\Views
 */
class ConferenceWideContainer extends BaseHtmlRenderable
{
    /**
     * @var RenderableInterface
     */
    protected $content;

    /**
     * Construct an instance of a ConferenceWideContainer.
     *
     * @param RenderableInterface $content
     */
    public function __construct(RenderableInterface $content)
    {
        parent::__construct();

        $this->content = $content;
    }

    /**
     * Render the object into a string.
     *
     * @return mixed
     */
    public function render()
    {
        return $this->content;
    }
}
