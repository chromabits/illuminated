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

use Chromabits\Nucleus\Data\Interfaces\SemigroupInterface;
use Chromabits\Nucleus\Foundation\BaseObject;
use Chromabits\Nucleus\Foundation\Interfaces\ArrayableInterface;
use Chromabits\Nucleus\Support\Std;

/**
 * Class RamlResponseBody.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Raml
 */
class RamlMessageBody extends BaseObject implements
    ArrayableInterface,
    SemigroupInterface
{
    /**
     * @var RamlBody[]
     */
    protected $bodyTypes;

    /**
     * Construct an instance of a RamlResponseBody.
     */
    public function __construct()
    {
        parent::__construct();

        $this->bodyTypes = [];
    }

    /**
     * @param string $mimeType
     * @param RamlBody $body
     *
     * @return RamlMessageBody
     */
    public function addType($mimeType, RamlBody $body)
    {
        $new = clone $this;

        $new->bodyTypes[$mimeType] = $body;

        return $new;
    }

    /**
     * @return RamlBody[]
     */
    public function getBodyTypes()
    {
        return $this->bodyTypes;
    }

    /**
     * @param RamlMessageBody|SemigroupInterface $other
     *
     * @return RamlMessageBody
     */
    public function append(SemigroupInterface $other)
    {
        $new = clone $this;

        foreach ($other->getBodyTypes() as $mimeType => $body) {
            $new = $new->addType($mimeType, $body);
        }

        return $new;
    }

    /**
     * Get an array representation of this object.
     *
     * @return array
     */
    public function toArray()
    {
        return Std::map(
            function (RamlBody $body) {
                return $body->toArray();
            },
            $this->bodyTypes
        );
    }
}
