<?php

namespace Chromabits\Illuminated\Conference\Views;

use Chromabits\Illuminated\Conference\Entities\ConferenceContext;
use Chromabits\Nucleus\View\BaseHtmlRenderable;
use Chromabits\Nucleus\View\Common\Anchor;

/**
 * Class ContextAnchor
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Conference\Views
 */
class ContextAnchor extends BaseHtmlRenderable
{
    /**
     * @var ConferenceContext
     */
    protected $context;

    /**
     * @var mixed
     */
    protected $content;

    /**
     * @var string
     */
    protected $module;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var bool
     */
    protected $secure;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * Construct an instance of a ContextAnchor.
     *
     * @param ConferenceContext $context
     */
    public function __construct(ConferenceContext $context)
    {
        parent::__construct();

        $this->context = $context;

        $this->parameters = [];
        $this->secure = null;
        $this->attributes = [];
    }

    /**
     * Render the object into a string.
     *
     * @return mixed
     */
    public function render()
    {
        if ($this->method) {
            return new Anchor(
                array_merge($this->attributes, [
                    'href' => $this->context->method(
                        $this->module,
                        $this->method,
                        $this->parameters,
                        $this->secure
                    )
                ]),
                $this->content
            );
        }

        return new Anchor(
            array_merge($this->attributes, [
                'href' => $this->context->module(
                    $this->module,
                    $this->parameters,
                    $this->secure
                )
            ]),
            $this->content
        );
    }

    /**
     * @param mixed $content
     *
     * @return ContextAnchor
     */
    public function withContent($content)
    {
        $copy = clone $this;

        $copy->content = $content;

        return $copy;
    }

    /**
     * @param string $module
     *
     * @return ContextAnchor
     */
    public function withModule($module)
    {
        $copy = clone $this;

        $copy->module = $module;

        return $copy;
    }

    /**
     * @param string $method
     *
     * @return ContextAnchor
     */
    public function withMethod($method)
    {
        $copy = clone $this;

        $copy->method = $method;

        return $copy;
    }

    /**
     * @param array $parameters
     *
     * @return ContextAnchor
     */
    public function withParameters($parameters)
    {
        $copy = clone $this;

        $copy->parameters = $parameters;

        return $copy;
    }

    /**
     * @param boolean $secure
     *
     * @return ContextAnchor
     */
    public function withSecure($secure)
    {
        $copy = clone $this;

        $copy->secure = $secure;

        return $copy;
    }

    /**
     * @param array $attributes
     *
     * @return ContextAnchor
     */
    public function withAttributes($attributes)
    {
        $copy = clone $this;

        $copy->attributes = $attributes;

        return $copy;
    }
}
