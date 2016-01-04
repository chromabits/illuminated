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

/**
 * Class RamlResponse.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Raml
 */
class RamlResponse extends BaseObject implements
    ArrayableInterface,
    SemigroupInterface
{
    /**
     * @var string
     */
    protected $description;

    /**
     * @var RamlMessageBody
     */
    protected $body;

    /**
     * Construct an instance of a RamlResponse.
     */
    public function __construct()
    {
        parent::__construct();

        $this->body = new RamlMessageBody();
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
     * @return RamlResponse
     */
    public function setDescription($description)
    {
        $new = clone $this;

        $new->description = $description;

        return $new;
    }

    /**
     * @return RamlMessageBody
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param RamlMessageBody $body
     *
     * @return RamlResponse
     */
    public function setBody($body)
    {
        $new = clone $this;

        $new->body = $body;

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
                'description' => $this->description,
                'body' => $this->body ? $this->body->toArray() : null,
            ]
        );
    }

    /**
     * Append another semigroup and return the result.
     *
     * @param RamlResponse|SemigroupInterface $other
     *
     * @return SemigroupInterface
     */
    public function append(SemigroupInterface $other)
    {
        $new = clone $this;

        $new->description = implode(
            '. ',
            [
                $this->description,
                $other->description,
            ]
        );

        $new->body = $this->body->append($other->body);

        return $new;
    }
}
