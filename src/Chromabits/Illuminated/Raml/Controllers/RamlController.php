<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Raml\Controllers;

use Chromabits\Illuminated\Foundation\Interfaces\ApplicationManifestInterface;
use Chromabits\Illuminated\Http\BaseController;
use Chromabits\Illuminated\Raml\Interfaces\RamlEncoderInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RamlController.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Raml\Controllers
 */
class RamlController extends BaseController
{
    /**
     * Generate and return a RAML file describing the current application
     * manifest.
     *
     * @param RamlEncoderInterface $ramlEncoder
     * @param ApplicationManifestInterface $manifest
     *
     * @return Response
     */
    public function getIndex(
        RamlEncoderInterface $ramlEncoder,
        ApplicationManifestInterface $manifest
    ) {
        return Response::create(
            $ramlEncoder->encode($manifest),
            Response::HTTP_OK,
            [
                'content-type' => 'application/yaml',
            ]
        );
    }
}
