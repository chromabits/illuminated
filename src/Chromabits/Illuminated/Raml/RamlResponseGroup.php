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

use Chromabits\Nucleus\Foundation\BaseObject;
use Chromabits\Nucleus\Foundation\Interfaces\ArrayableInterface;
use Chromabits\Nucleus\Support\Arr;
use Chromabits\Nucleus\Support\Std;

/**
 * Class RamlResponseGroup.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Raml
 */
class RamlResponseGroup extends BaseObject implements ArrayableInterface
{
    /**
     * @var RamlResponse[]
     */
    protected $responses;

    /**
     * Construct an instance of a RamlResponseGroup.
     */
    public function __construct()
    {
        parent::__construct();

        $this->responses = [];
    }

    /**
     * @param int $code
     * @param RamlResponse $response
     *
     * @return RamlResponseGroup
     */
    public function addResponse($code, RamlResponse $response)
    {
        $new = clone $this;

        if (Arr::has($this->responses, $code)) {
            $new->responses[$code]
                = $this->responses[$code]->append($response);

            return $new;
        }

        $new->responses[$code] = $response;

        return $new;
    }

    /**
     * @return RamlResponse[]
     */
    public function getResponses()
    {
        return $this->responses;
    }

    /**
     * Get an array representation of this object.
     *
     * @return array
     */
    public function toArray()
    {
        return Std::map(
            function (RamlResponse $response) {
                return $response->toArray();
            },
            $this->responses
        );
    }
}
