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

use Chromabits\Nucleus\Foundation\BaseObject;
use Chromabits\Nucleus\Support\Html;
use Chromabits\Nucleus\Support\Std;
use Chromabits\Nucleus\View\Bootstrap\CardBlock;
use Chromabits\Nucleus\View\Interfaces\RenderableInterface;
use Illuminate\Contracts\Pagination\Paginator;

/**
 * Class ConferencePaginator.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Conference\Views
 */
class ConferencePaginator extends BaseObject implements
    RenderableInterface
{
    /**
     * @var Paginator
     */
    protected $paginator;

    /**
     * Construct an instance of a ConferencePaginator.
     *
     * @param Paginator $paginator
     */
    public function __construct(Paginator $paginator)
    {
        parent::__construct();

        $this->paginator = $paginator;
    }

    /**
     * Render the object into a string.
     *
     * @return mixed
     */
    public function render()
    {
        return Std::firstBias(
            $this->paginator->hasPages(),
            function () {
                return new CardBlock(
                    ['class' => 'card-block text-center'],
                    new BootstrapFourPaginatorPresenter($this->paginator)
                );
            },
            ''
        );
    }
}
