<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Meditation;

use Chromabits\Illuminated\Database\Articulate\Model;
use Chromabits\Nucleus\Exceptions\LackOfCoffeeException;
use Chromabits\Nucleus\Foundation\BaseObject;
use Chromabits\Nucleus\Meditation\TypeHound;
use Chromabits\Nucleus\Support\Arr;
use Chromabits\Nucleus\Support\Std;
use Illuminate\Database\Eloquent\Relations\Relation;
use ReflectionMethod;

/**
 * Class ModelInspector.
 *
 * The inspector class can perform a few kinds of introspection over
 * Illuminated models.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Meditation
 */
class ModelInspector extends BaseObject
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * Mini-library of all the relationships of this model.
     *
     * @var array
     */
    protected $relations = null;

    /**
     * Construct an instance of a ModelInspector.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        parent::__construct();

        $this->model = $model;
    }

    /**
     * Return whether a model is related in any way to another model.
     *
     * An optional type containing the relation class name can be passed to
     * narrow down the search by relationship type (BelongsTo::class, etc).
     *
     * @param string $modelClass
     * @param null|string $type
     *
     * @return bool
     */
    public function isRelatedTo($modelClass, $type = null)
    {
        return count($this->getRelationsTo($modelClass, $type)) > 0;
    }

    /**
     * Get all the relationships a model has with another model.
     *
     * An optional type containing the relation class name can be passed to
     * narrow down the search by relationship type (BelongsTo::class, etc).
     *
     * @param string $modelClass
     * @param null|string $type
     *
     * @return array|Relation[]
     */
    public function getRelationsTo($modelClass, $type = null)
    {
        $relations = $this->discoverRelations();

        $relations = Std::filter(
            function (Relation $relation) use ($modelClass) {
                return (get_class($relation->getRelated()) ===  $modelClass);
            },
            $relations
        );

        if ($type !== null) {
            $relations = Std::filter(
                function (Relation $relation) use ($type) {
                    return get_class($relation) === $type;
                },
                $relations
            );
        }

        return $relations;
    }

    /**
     * Get a map of relation names and an instance of the Relation.
     *
     * @return Relation[]
     */
    public function getRelations()
    {
        return $this->discoverRelations();
    }

    /**
     * Get a specific relationship by its name.
     *
     * @param string $name
     *
     * @throws LackOfCoffeeException
     * @return Relation
     */
    public function getRelation($name)
    {
        $relations = $this->discoverRelations();

        if (!Arr::has($relations, $name)) {
            throw new LackOfCoffeeException(vsprintf(
                'The model does not declare a dependency named "%s".',
                [$name]
            ));
        }

        return $relations[$name];
    }

    /**
     * Validate and discover all the relationships on a model.
     *
     * @return Relation[]
     */
    protected function discoverRelations()
    {
        if ($this->relations === null) {
            $this->relations = [];

            Std::each(function ($name) {
                // Check that the relationship method is there.
                if (!method_exists($this->model, $name)) {
                    throw new LackOfCoffeeException(vsprintf(
                        'The model declares a relationship named "%s" but' .
                        ' there is no method with that name.',
                        [$name]
                    ));
                }

                $relationReflector = new ReflectionMethod(
                    $this->model,
                    $name
                );

                if (!$relationReflector->isPublic()) {
                    throw new LackOfCoffeeException(vsprintf(
                        'The method for the relationship named "%s" should' .
                        ' be public.',
                        [$name]
                    ));
                }

                $argumentCount = $relationReflector->getNumberOfParameters();
                if ($argumentCount > 0) {
                    throw new LackOfCoffeeException(vsprintf(
                        'The method for the relationship named "%s" should' .
                        ' take 0 arguments. However, it requires %d.',
                        [$name, $argumentCount]
                    ));
                }

                $relation = $this->model->$name();

                if (!$relation instanceof Relation) {
                    throw new LackOfCoffeeException(vsprintf(
                        'The method for the relationship named "%s" should' .
                        ' return an instance of %s. Got %s.',
                        [$name, Relation::class, TypeHound::fetch($relation)]
                    ));
                }

                $this->relations[$name] = $relation;
            }, $this->model->getRelated());
        }

        return $this->relations;
    }
}
