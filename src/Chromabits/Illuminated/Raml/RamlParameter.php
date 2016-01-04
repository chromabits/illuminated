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

use Chromabits\Illuminated\Raml\Enums\RamlTypes;
use Chromabits\Nucleus\Foundation\BaseObject;
use Chromabits\Nucleus\Foundation\Interfaces\ArrayableInterface;
use Chromabits\Nucleus\Meditation\Arguments;
use Chromabits\Nucleus\Meditation\Boa;

/**
 * Class RamlParameters.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Raml
 */
class RamlParameter extends BaseObject implements ArrayableInterface
{
    /**
     * @var string
     */
    protected $displayName;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $enum;

    /**
     * @var string
     */
    protected $pattern;

    /**
     * @var int
     */
    protected $minLength;

    /**
     * @var int
     */
    protected $maxLength;

    /**
     * @var int|float
     */
    protected $minimum;

    /**
     * @var int|float
     */
    protected $maximum;

    /**
     * @var mixed
     */
    protected $example;

    /**
     * @var bool
     */
    protected $repeat;

    /**
     * @var bool
     */
    protected $required;

    /**
     * @var mixed
     */
    protected $default;

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @param string $displayName
     *
     * @return RamlParameter
     */
    public function setDisplayName($displayName)
    {
        $new = clone $this;

        $new->displayName = $displayName;

        return $new;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return RamlParameter
     */
    public function setDescription($description)
    {
        $new = clone $this;

        $new->description = $description;

        return $new;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return RamlParameter
     */
    public function setType($type)
    {
        Arguments::define(Boa::in(RamlTypes::getValues()))->check($type);

        $new = clone $this;

        $new->type = $type;

        return $new;
    }

    /**
     * @return array
     */
    public function getEnum()
    {
        return $this->enum;
    }

    /**
     * @param array $enum
     *
     * @return RamlParameter
     */
    public function setEnum($enum)
    {
        $new = clone $this;

        $new->enum = $enum;

        return $new;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @param string $pattern
     *
     * @return RamlParameter
     */
    public function setPattern($pattern)
    {
        $new = clone $this;

        $new->pattern = $pattern;

        return $new;
    }

    /**
     * @return int
     */
    public function getMinLength()
    {
        return $this->minLength;
    }

    /**
     * @param int $minLength
     *
     * @return RamlParameter
     */
    public function setMinLength($minLength)
    {
        $new = clone $this;

        $new->minLength = $minLength;

        return $new;
    }

    /**
     * @return int
     */
    public function getMaxLength()
    {
        return $this->maxLength;
    }

    /**
     * @param int $maxLength
     *
     * @return RamlParameter
     */
    public function setMaxLength($maxLength)
    {
        $new = clone $this;

        $new->maxLength = $maxLength;

        return $new;
    }

    /**
     * @return float|int
     */
    public function getMinimum()
    {
        return $this->minimum;
    }

    /**
     * @param float|int $minimum
     *
     * @return RamlParameter
     */
    public function setMinimum($minimum)
    {
        $new = clone $this;

        $new->minimum = $minimum;

        return $new;
    }

    /**
     * @return float|int
     */
    public function getMaximum()
    {
        return $this->maximum;
    }

    /**
     * @param float|int $maximum
     *
     * @return RamlParameter
     */
    public function setMaximum($maximum)
    {
        $new = clone $this;

        $new->maximum = $maximum;

        return $new;
    }

    /**
     * @return mixed
     */
    public function getExample()
    {
        return $this->example;
    }

    /**
     * @param mixed $example
     *
     * @return RamlParameter
     */
    public function setExample($example)
    {
        $new = clone $this;

        $new->example = $example;

        return $new;
    }

    /**
     * @return bool
     */
    public function isRepeat()
    {
        return $this->repeat;
    }

    /**
     * @param bool $repeat
     *
     * @return RamlParameter
     */
    public function setRepeat($repeat)
    {
        $new = clone $this;

        $new->repeat = $repeat;

        return $new;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @param bool $required
     *
     * @return RamlParameter
     */
    public function setRequired($required)
    {
        $new = clone $this;

        $new->required = $required;

        return $new;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param mixed $default
     *
     * @return RamlParameter
     */
    public function setDefault($default)
    {
        $new = clone $this;

        $new->default = $default;

        return $new;
    }

    /**
     * Get an array representation of this object.
     *
     * @return array
     */
    public function toArray()
    {
        return RamlUtils::filterEmptyValues(
            [
                'displayName' => $this->displayName,
                'description' => $this->description,
                'type' => $this->type,
                'enum' => $this->enum,
                'pattern' => $this->pattern,
                'minLength' => $this->minLength,
                'maxLength' => $this->maxLength,
                'minimum' => $this->minimum,
                'maximum' => $this->maximum,
                'example' => $this->example,
                'repeat' => $this->repeat,
                'required' => $this->required,
                'default' => $this->default,
            ]
        );
    }
}
