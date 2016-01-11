<?php

namespace Chromabits\Illuminated\Jobs\Requests;

use Chromabits\Nucleus\Meditation\Boa;
use Chromabits\Nucleus\Meditation\Interfaces\CheckableInterface;

/**
 * Class CancelJobModuleRequest
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Jobs\Requests
 */
class CancelJobModuleRequest extends DescribeJobModuleRequest
{
    /**
     * @return CheckableInterface
     */
    public function getCheckable()
    {
        return parent::getCheckable()
            ->withFieldType('confirm', Boa::string())
            ->withFieldConstraints('confirm', [Boa::booleanLike()]);
    }
}
