<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Database\Articulate;

use Chromabits\Nucleus\Exceptions\LackOfCoffeeException;

/**
 * Class JsonModel.
 *
 * An extension of Eloquent capable of saving some fields as JSON arrays in the
 * database.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @deprecated L5.1 supports attribute type-casting
 * @package Chromabits\Illuminated\Database\Articulate
 */
class JsonModel extends Model
{
    protected static $registered = [];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     *
     * @throws LackOfCoffeeException
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (!array_key_exists(static::class, static::$registered)) {
            throw new LackOfCoffeeException(
                vsprintf(
                    'You forgot to call registerEvents() on %s.'
                    . ' The method should be called on a Service Provider.'
                    . ' See http://laravel.com/docs/5.1/eloquent#events'
                    . ' for more details.',
                    [static::class]
                )
            );
        }
    }

    /**
     * Fields that should be handled as JSON in the database.
     *
     * @var string[]
     */
    protected $json = [];

    /**
     * Register model events.
     */
    public static function registerEvents()
    {
        // Define a handler for converting the specified properties into JSON
        // before saving the model to the database
        static::saving(
            function (JsonModel $model) {
                foreach ($model->getJsonFields() as $field) {
                    $model->$field = json_encode($model->$field);
                }
            }
        );

        // Restore the specified properties back into arrays after saving
        static::saved(
            function (JsonModel $model) {
                foreach ($model->getJsonFields() as $field) {
                    $model->$field = json_decode($model->$field, true);
                }
            }
        );

        static::$registered[static::class] = true;
    }

    /**
     * @inheritdoc
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
        $model = parent::newFromBuilder($attributes, $connection = null);

        foreach ($this->getJsonFields() as $field) {
            $model->$field = json_decode($model->$field, true);
        }

        return $model;
    }

    /**
     * Get which fields should be handled as JSON in the database.
     *
     * @return array
     */
    public function getJsonFields()
    {
        return $this->json;
    }
}
