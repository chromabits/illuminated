<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Raml\Enums;

use Chromabits\Nucleus\Support\Enum;

/**
 * Class RamlTypes.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Raml\Enums
 */
class RamlTypes extends Enum
{
    const TYPE_STRING = 'string';

    const TYPE_NUMBER = 'number';

    const TYPE_INTEGER = 'integer';

    const TYPE_DATE = 'date';

    const TYPE_BOOLEAN = 'boolean';

    const TYPE_FILE = 'file';
}
