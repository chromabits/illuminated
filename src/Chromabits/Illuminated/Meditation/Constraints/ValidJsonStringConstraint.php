<?php

namespace Chromabits\Illuminated\Meditation\Constraints;

use Chromabits\Nucleus\Meditation\Constraints\AbstractConstraint;

/**
 * Class ValidJsonStringConstraint
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Meditation\Constraints
 */
class ValidJsonStringConstraint extends AbstractConstraint
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
        return json_decode($value) !== null;
    }

    /**
     * Get string representation of this constraint.
     *
     * @return mixed
     */
    public function toString()
    {
        return '{JSON string}';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'The value is expected to be valid JSON';
    }
}
