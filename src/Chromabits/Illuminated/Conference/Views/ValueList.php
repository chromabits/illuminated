<?php

namespace Chromabits\Illuminated\Conference\Views;



/**
 * Class ValueList
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Conference\Views
 */
class ValueList extends DescriptionList
{
    /**
     * @param string $term
     * @param mixed $content
     *
     * @return ValueList
     */
    public function addTerm($term, $content)
    {
        return parent::addTerm($term, new ValuePresenter($content));
    }
}
