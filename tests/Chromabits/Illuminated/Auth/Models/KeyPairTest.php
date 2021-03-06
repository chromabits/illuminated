<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Auth\Models;

use Tests\Chromabits\Illuminated\Auth\AuthDatabaseTrait;
use Tests\Chromabits\Support\IlluminatedTestCase;

/**
 * Class KeyPairTest.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Auth\Models
 */
class KeyPairTest extends IlluminatedTestCase
{
    use AuthDatabaseTrait;

    /**
     * Setup testing environment.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->migrateAuthDatabase();
        KeyPair::registerEvents();
    }

    public function testJsonProperties()
    {
        $pair = new KeyPair();

        $pair->public_id = 'wowowowow';
        $pair->secret_key = 'omgomgomgomgomg';
        $pair->type = 'testing';
        $pair->data = [
            'name' => 'dolan',
        ];

        $pair->save();

        $pair2 = KeyPair::query()
            ->where('public_id', 'wowowowow')
            ->firstOrFail();

        $this->assertEquals([
            'name' => 'dolan',
        ], $pair2->data);
    }
}
