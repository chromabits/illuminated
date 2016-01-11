<?php

namespace Chromabits\Illuminated\Meditation\Constraints;

use Carbon\Carbon;
use Chromabits\Nucleus\Meditation\Constraints\AbstractConstraint;
use Exception;

/**
 * Class ValidDateTimeConstraint
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Meditation\Constraints
 */
class DateTimeStringConstraint extends AbstractConstraint
{
    /**
     * Check if the constraint is met.
     *
     * @param mixed $value
     * @param array $context
     *
     * @return mixed
     */
    public function check($value, array $context = [])
    {
        try {
            Carbon::parse($value);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get string representation of this constraint.
     *
     * @return mixed
     */
    public function toString()
    {
        return '{DateTime string}';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'The value is expected to be a string that can be parsed ' .
            'into a DateTime string';
    }
}
