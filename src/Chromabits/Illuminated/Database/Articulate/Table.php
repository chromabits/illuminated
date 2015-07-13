<?php

/**
 * Copyright 2015, Eduardo Trujillo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Laravel Helpers package
 */

namespace Chromabits\Illuminated\Database\Articulate;

/**
 * Class Table
 *
 * A barebones abstraction of a database table.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Database\Articulate
 */
class Table
{
    /**
     * The name of the table.
     *
     * @var string
     */
    protected $name;

    /**
     * Construct an instance of a Table.
     *
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Get the name of the table.
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the full name of a table field (for joins, etc).
     *
     * @param $name
     *
     * @return string
     */
    public function field($name)
    {
        return $this->name . '.' . $name;
    }
}
