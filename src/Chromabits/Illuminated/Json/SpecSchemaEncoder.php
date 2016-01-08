<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Json;

use Chromabits\Nucleus\Exceptions\CoreException;
use Chromabits\Nucleus\Foundation\BaseObject;
use Chromabits\Nucleus\Foundation\Interfaces\ArrayableInterface;
use Chromabits\Nucleus\Meditation\Constraints\AbstractConstraint;
use Chromabits\Nucleus\Meditation\Constraints\PrimitiveTypeConstraint;
use Chromabits\Nucleus\Meditation\Primitives\CompoundTypes;
use Chromabits\Nucleus\Meditation\Spec;
use Chromabits\Nucleus\Support\Arr;

/**
 * Class SpecSchemaEncoder.
 *
 * Generates JSON schemas from Nucleus specs.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Json
 */
class SpecSchemaEncoder extends BaseObject
{
    /**
     * Attempt to encode a Spec into a JSON schema.
     *
     * @param Spec $spec
     * @param string $title
     *
     * @throws CoreException
     * @return string
     */
    public function encode(Spec $spec, $title = 'root')
    {
        $schema = [
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => $title,
            'type' => CompoundTypes::COMPOUND_OBJECT,
            'required' => $spec->getRequired(),
        ];
        $properties = [];

        foreach ($spec->getConstraints() as $key => $constraints) {
            $properties[$key] = $this->encodeConstraints(
                $spec,
                $constraints,
                $key
            );
        }

        foreach ($spec->getDefaults() as $key => $default) {
            if (!Arr::has($properties, $key)) {
                $properties[$key] = [];
            }

            $properties[$key]['default'] = $default;
        }

        $schema['properties'] = $properties;

        return json_encode($schema, JSON_PRETTY_PRINT);
    }

    /**
     * Encode constraints into JSON schema types/constraints.
     *
     * @param Spec $spec
     * @param AbstractConstraint|AbstractConstraint[]|Spec $input
     * @param string $title
     *
     * @throws CoreException
     * @return array
     */
    public function encodeConstraints(Spec $spec, $input, $title)
    {
        if ($input instanceof AbstractConstraint) {
            $schema = [
                'description' => $input->getDescription(),
            ];

            if ($input instanceof PrimitiveTypeConstraint) {
                $schema['type'] = $input->toString();
            }

            return $schema;
        } elseif (is_array($input) || $input instanceof ArrayableInterface) {
            $schema = [];
            $descriptions = [];

            if ($input instanceof ArrayableInterface) {
                $input = $input->toArray();
            }

            foreach ($input as $constraint) {
                $descriptions[] = $constraint->getDescription();

                if ($constraint instanceof PrimitiveTypeConstraint) {
                    $schema['type'] = $constraint->toString();
                }
            }

            $schema['description'] = implode('. ', $descriptions);

            return $schema;
        } elseif ($input instanceof Spec) {
            return (new static())->encode($input, $title);
        }

        throw new CoreException('Unexpected constraint type.');
    }
}
