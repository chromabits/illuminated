<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Raml;

use Chromabits\Nucleus\Foundation\StaticObject;
use Chromabits\Nucleus\Support\Arr;

/**
 * Class RamlUtils.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Raml
 */
class RamlUtils extends StaticObject
{
    /**
     * Only return non-null and non-empty values in an array.
     *
     * @param array $properties
     * @param array|null $allowed
     *
     * @return array
     */
    public static function filterEmptyValues($properties, array $allowed = null)
    {
        // If provided, only use allowed properties
        $properties = Arr::only($properties, $allowed);

        return array_filter(
            $properties,
            function ($value) {
                if (is_array($value) && count($value) === 0) {
                    return false;
                }

                return !is_null($value);
            }
        );
    }
}
