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

use Chromabits\Illuminated\Http\Factories\ResourceFactory;
use Chromabits\Illuminated\Http\ResourceAggregator;
use Chromabits\Illuminated\Raml\Controllers\RamlController;

/**
 * Class RamlResourceAggregator.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Raml
 */
class RamlResourceAggregator extends ResourceAggregator
{
    /**
     * Get resources to be aggregated.
     *
     * @return ResourceFactory[]
     */
    public function getResources()
    {
        return [
            ResourceFactory::create(RamlController::class)
                ->get('/api.raml', 'getIndex'),
        ];
    }
}
