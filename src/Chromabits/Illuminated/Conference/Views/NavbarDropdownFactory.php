<?php

namespace Chromabits\Illuminated\Conference\Views;

use Chromabits\Nucleus\View\Bootstrap\DropdownFactory;
use Chromabits\Nucleus\View\Common\Anchor;
use Chromabits\Nucleus\View\Common\Div;
use Chromabits\Nucleus\View\Common\Italic;

/**
 * Class NavbarDropdownFactory.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Conference\Views
 */
class NavbarDropdownFactory extends DropdownFactory
{
    /**
     * @var mixed
     */
    protected $content;

    /**
     * Construct an instance of a NavbarDropdownFactory.
     */
    public function __construct()
    {
        parent::__construct();

        $this->content = new Italic(['class' => 'fa fa-ellipsis-h']);
    }

    /**
     * Set content.
     *
     * @param mixed $content
     *
     * @return NavbarDropdownFactory
     */
    public function setContent($content)
    {
        $copy = clone $this;

        $copy->content = $content;

        return $copy;
    }

    /**
     * Build the dropdown element.
     *
     * @return Div
     */
    public function make()
    {
        $menuClasses = ['dropdown-menu'];

        if ($this->right) {
            $menuClasses[] = 'dropdown-menu-right';
        }

        return new Div(['class' => 'dropdown nav-item'], [
            new Anchor(
                [
                    'id' => $this->hash,
                    'class' => 'nav-link',
                    'data-toggle' => 'dropdown',
                    'aria-haspopup' => 'true',
                    'aria-expanded' => 'false',
                ],
                $this->content
            ),
            new Div(
                [
                    'class' => $menuClasses,
                    'aria-labelledby' => $this->hash,
                ],
                $this->options
            ),
        ]);
    }
}