<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Database;

use Chromabits\Illuminated\Database\Interfaces\BaseRepositoryInterface;
use Chromabits\Illuminated\Meditation\Constraints\ExactlyOneRecordConstraint;
use Chromabits\Nucleus\Exceptions\LackOfCoffeeException;
use Chromabits\Nucleus\Foundation\BaseObject;
use Chromabits\Nucleus\Meditation\Arguments;
use Chromabits\Nucleus\Meditation\Boa;
use Chromabits\Nucleus\Meditation\Exceptions\InvalidArgumentException;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseRepository.
 *
 * A base repository class for manipulating models.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Database
 */
abstract class BaseRepository extends BaseObject implements
    BaseRepositoryInterface
{
    /**
     * @var string
     */
    protected $model;

    /**
     * Get a spec constraint for ensuring that the model exists.
     *
     * The field name defaults to `id`.
     *
     * @param string $idField
     *
     * @throws LackOfCoffeeException
     * @throws InvalidArgumentException
     * @return ExactlyOneRecordConstraint
     */
    public function makeExistsConstraint($idField = 'id')
    {
        Arguments::define(Boa::string())->check($idField);

        return new ExactlyOneRecordConstraint(
            $this->makeModelInstance()->query()->getQuery(),
            ['id' => $idField]
        );
    }

    /**
     * Make an instance of a the model for this repository.
     *
     * @throws LackOfCoffeeException
     * @return Model
     */
    protected function makeModelInstance()
    {
        $instance = new $this->model();

        if (!$instance instanceof Model) {
            throw new LackOfCoffeeException(
                'The model instance should extend Illuminate/Database/Model.'
            );
        }

        return $instance;
    }

    /**
     * Check whether or not the model with the specified ID exists.
     *
     * @param int $id
     *
     * @throws InvalidArgumentException
     * @throws LackOfCoffeeException
     * @return bool
     */
    public function exists($id)
    {
        Arguments::define(Boa::integer())->check($id);

        return $this->makeModelInstance()->query()
            ->where(['id' => $id])
            ->first() !== null;
    }

    /**
     * Perform a simple where query and return a collection of the matching
     * models.
     *
     * @param array $fieldConditions
     * @param array $columns
     * @param array $with
     *
     * @return Collection
     */
    public function where(
        array $fieldConditions,
        array $columns = ['*'],
        array $with = []
    ) {
        return $this->makeWhereQuery($fieldConditions, $columns, $with)
            ->get($columns);
    }

    /**
     * Make a simple where query.
     *
     * @param array $fieldConditions
     * @param array $columns
     * @param array $with
     *
     * @throws InvalidArgumentException
     * @throws LackOfCoffeeException
     * @return Builder
     */
    protected function makeWhereQuery(
        array $fieldConditions,
        array $columns = ['*'],
        array $with = []
    ) {
        Arguments::define(
            Boa::arr(),
            Boa::arrOf(Boa::string()),
            Boa::arrOf(Boa::string())
        )->check($fieldConditions, $columns, $with);

        $query = $this->makeModelInstance()->query()
            ->where($fieldConditions);

        $this->applyWith($query, $with);

        return $query;
    }

    /**
     * Apply a with condition to the query.
     *
     * @param Builder $query
     * @param array $with
     */
    protected function applyWith(Builder $query, $with = [])
    {
        if (count($with)) {
            $query->with($with);
        }
    }

    /**
     * Perform a simple where query and return a single element matching the
     * query, or fail.
     *
     * @param array $fieldConditions
     * @param array $columns
     * @param array $with
     *
     * @return Collection
     */
    public function whereFirstOrFail(
        array $fieldConditions,
        array $columns = ['*'],
        array $with = []
    ) {
        return $this->makeWhereQuery($fieldConditions, $columns, $with)
            ->firstOrFail($columns);
    }

    /**
     * Perform a simple where query and return a paginator of the matching
     * models.
     *
     * @param array $fieldConditions
     * @param array $columns
     * @param array $with
     * @param int $take
     * @param string $pageName
     * @param null|int $page
     *
     * @return LengthAwarePaginator
     */
    public function wherePaginated(
        array $fieldConditions,
        array $columns = ['*'],
        array $with = [],
        $take = 25,
        $pageName = 'page',
        $page = null
    ) {
        return $this->makeWhereQuery($fieldConditions, $columns, $with)
            ->paginate($take, $columns, $pageName, $page);
    }

    /**
     * Get all models.
     *
     * This is a fairly dumb function unless you have a really small table.
     * You may run of memory or query might never finish. Use with care.
     *
     * @param array $columns
     * @param array $with
     *
     * @throws LackOfCoffeeException
     * @return Collection
     */
    public function getAll($columns = ['*'], $with = [])
    {
        Arguments::define(
            Boa::arrOf(Boa::string()),
            Boa::arrOf(Boa::string())
        )->check($columns, $with);

        $query = $this->makeModelInstance()->query();

        $this->applyWith($query, $with);

        return $query->get($columns);
    }

    /**
     * Get all models paginated.
     *
     * @param array $columns
     * @param array $with
     * @param int $take
     * @param string $pageName
     * @param null|int $page
     *
     * @throws LackOfCoffeeException
     * @return LengthAwarePaginator
     */
    public function getAllPaginated(
        $columns = ['*'],
        $with = [],
        $take = 25,
        $pageName = 'page',
        $page = null
    ) {
        Arguments::define(
            Boa::arrOf(Boa::string()),
            Boa::arrOf(Boa::string()),
            Boa::integer(),
            Boa::string(),
            Boa::either(Boa::null(), Boa::integer())
        )->check($columns, $with, $take, $pageName, $page);

        $query = $this->makeModelInstance()->query();

        $this->applyWith($query, $with);

        return $query->paginate($take, $columns, $pageName, $page);
    }

    /**
     * Find a model by its id and then update its contents.
     *
     * @param int $id
     * @param array $fill
     *
     * @return Model
     */
    public function updateById($id, array $fill)
    {
        $instance = $this->getById($id);

        // TODO: Add support for validating the model against a set of specs.

        $instance->update($fill);

        return $instance;
    }

    /**
     * Get a model by its id.
     *
     * @param int $id
     * @param array $columns
     * @param array $with
     *
     * @throws InvalidArgumentException
     * @throws LackOfCoffeeException
     * @return Model
     */
    public function getById($id, array $columns = ['*'], array $with = [])
    {
        Arguments::define(
            Boa::integer(),
            Boa::arrOf(Boa::string()),
            Boa::arrOf(Boa::string())
        )->check($id, $columns, $with);

        $query = $this->makeModelInstance()->query()
            ->where('id', $id);

        $this->applyWith($query, $with);

        return $query->firstOrFail($columns);
    }

    /**
     * Create and save a new model with the provided attributes.
     *
     * @param array $attributes
     *
     * @return Model
     * @throws LackOfCoffeeException
     */
    public function create($attributes = [])
    {
        return $this->makeModelInstance()->create($attributes);
    }

    /**
     * Delete a model.
     *
     * @param int $id
     *
     * @return bool|null
     * @throws Exception
     */
    public function deleteById($id)
    {
        return $this->getById($id, ['id'])->delete();
    }
}
