<?php

namespace Chromabits\Illuminated\Conference\Views;

use Carbon\Carbon;
use Chromabits\Nucleus\Support\Html;
use Chromabits\Nucleus\View\BaseHtmlRenderable;
use Chromabits\Nucleus\View\Interfaces\SafeHtmlProducerInterface;

/**
 * Class ValuePresenter
 *
 * Renders different values with sensible defaults.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Conference\Views
 */
class ValuePresenter extends BaseHtmlRenderable
{
    /**
     * @var mixed
     */
    protected $content;

    /**
     * Construct an instance of a ValuePresenter.
     *
     * @param mixed $content
     */
    public function __construct($content)
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
        if (is_string($this->content)) {
            return Html::escape($this->content);
        } elseif ($this->content instanceof Carbon) {
            return Html::escape($this->content->toDayDateTimeString());
        } elseif (is_bool($this->content)) {
            if ($this->content) {
                return 'True';
            }

            return 'False';
        } elseif (is_float($this->content)) {
            return vsprintf('%.2f', [$this->content]);
        } elseif (is_integer($this->content)) {
            return vsprintf('%d', [ $this->content]);
        } elseif ($this->content instanceof SafeHtmlProducerInterface) {
            return $this->content->getSafeHtml();
        } elseif ($this->content === null) {
            return 'NULL';
        }

        return 'Object{ ... }';
    }
}
