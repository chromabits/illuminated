<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Conference\Views;

use Chromabits\Illuminated\Alerts\Alert;
use Chromabits\Illuminated\Contracts\Alerts\AlertManager;
use Chromabits\Nucleus\Data\ArrayList;
use Chromabits\Nucleus\Data\ArrayMap;
use Chromabits\Nucleus\Foundation\BaseObject;
use Chromabits\Nucleus\Foundation\Interfaces\ArrayableInterface;
use Chromabits\Nucleus\Meditation\Constraints\AbstractConstraint;
use Chromabits\Nucleus\Support\Std;
use Chromabits\Nucleus\View\Common\Bold;
use Chromabits\Nucleus\View\Common\Div;
use Chromabits\Nucleus\View\Common\Paragraph;
use Chromabits\Nucleus\View\Interfaces\RenderableInterface;

/**
 * Class AlertPresenter.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Conference\Views
 */
class AlertPresenter extends BaseObject implements RenderableInterface
{
    /**
     * @var array
     */
    protected $alerts;

    /**
     * Construct an instance of a AlertPresenter.
     *
     * @param AlertManager $manager
     */
    public function __construct(AlertManager $manager)
    {
        parent::__construct();

        $this->alerts = $manager->all();
    }

    /**
     * Render the object into a string.
     *
     * @return mixed
     */
    public function render()
    {
        return new Div([], Std::map(function (Alert $alert) {
            $classes = ['alert'];

            switch ($alert->getType()) {
                case Alert::TYPE_SUCCESS:
                    $classes[] = 'alert-success';
                    break;
                case Alert::TYPE_WARNING:
                    $classes[] = 'alert-warning';
                    break;
                case Alert::TYPE_INFO:
                    $classes[] = 'alert-info';
                    break;
                case Alert::TYPE_ERROR:
                case Alert::TYPE_VALIDATION:
                    $classes[] = 'alert-danger';
                    break;
            }

            return new Div(
                ['class' => $classes],
                $this->simplifyContent($alert->getContent())
            );
        }, $this->alerts));
    }

    /**
     * Simplify the alert content into something Node can render.
     *
     * @param string|array $content
     *
     * @return array
     */
    protected function simplifyContent($content)
    {
        if (is_string($content)) {
            return $content;
        } elseif (is_array($content)
            || $content instanceof ArrayableInterface
        ) {
            return ArrayMap::of($content)
                ->map(function ($value, $key) {
                    if (is_numeric($key)) {
                        return $this->renderMessages($value);
                    }

                    return new Paragraph([], [
                        new Bold([], vsprintf('%s: ', [$key])),
                        $this->renderMessages($value)
                    ]);
                })
                ->toList();
        }

        return $content;
    }

    /**
     * Render constraint into a string.
     *
     * @param AbstractConstraint|AbstractConstraint[] $constraints
     *
     * @return string
     */
    protected function renderMessages($constraints)
    {
        if ($constraints instanceof AbstractConstraint) {
            return $constraints->getDescription();
        }

        return ArrayList::of($constraints)
            ->map(function (AbstractConstraint $constraint) {
                return $constraint->getDescription();
            })
            ->join('. ');
    }
}
