<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Database\Articulate;

use Chromabits\Nucleus\Foundation\BaseObject;
use Chromabits\Nucleus\Meditation\Exceptions\FailedCheckException;

/**
 * Class ValidatingObserver.
 *
 * Originally from: https://github.com/AltThree/Validator/
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Database\Articulate
 */
class ValidatingObserver extends BaseObject
{
    /**
     * Validate the model on saving.
     *
     * @param Model $model
     */
    public function saving(Model $model)
    {
        $this->validate($model);
    }

    /**
     * Validate the model on saving.
     *
     * @param Model $model
     */
    public function restoring(Model $model)
    {
        $this->validate($model);
    }

    protected function validate(Model $model)
    {
        $attributes = $model->attributesToArray();
        $checkable = $model->getCheckable();

        if ($checkable === null) {
            return;
        }

        $result = $checkable->check($attributes);

        if ($result->failed()) {
            throw new FailedCheckException($checkable, $result);
        }
    }
}
