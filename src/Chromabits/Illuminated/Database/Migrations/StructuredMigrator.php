<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Database\Migrations;

use Chromabits\Illuminated\Database\Interfaces\StructuredMigratorInterface;
use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;

/**
 * Class StructuredMigrator.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Database\Migrations
 */
class StructuredMigrator implements StructuredMigratorInterface
{
    /**
     * The migration repository implementation.
     *
     * @var MigrationRepositoryInterface
     */
    protected $repository;

    /**
     * The connection resolver instance.
     *
     * @var ConnectionResolverInterface
     */
    protected $resolver;

    /**
     * The name of the default connection.
     *
     * @var string
     */
    protected $connection;

    /**
     * Migration batch to run.
     *
     * @var Batch
     */
    protected $batch;

    /**
     * The notes for the current operation.
     *
     * @var array
     */
    protected $notes = [];

    /**
     * Create a new migrator instance.
     *
     * @param MigrationRepositoryInterface $repository
     * @param ConnectionResolverInterface $resolver
     * @param Batch $batch
     */
    public function __construct(
        MigrationRepositoryInterface $repository,
        ConnectionResolverInterface $resolver,
        Batch $batch
    ) {
        $this->resolver = $resolver;
        $this->repository = $repository;
        $this->batch = $batch;
    }

    /**
     * Run the outstanding migrations at a given path.
     *
     * @param bool $pretend
     *
     */
    public function run($pretend = false)
    {
        $this->notes = [];

        $this->batch->validate();
        $defined = $this->batch->getExpanded();

        // Once we discovered all the migrations in the batch, we will compare
        // them against the migrations that have already been run for this
        // package then run each of the outstanding migrations against a
        // database connection.
        $ran = $this->repository->getRan();

        $migrations = array_diff($defined, $ran);

        $this->runMigrationList($migrations, $pretend);
    }

    /**
     * Run an array of migrations.
     *
     * @param  array $migrations
     * @param  bool $pretend
     *
     */
    public function runMigrationList($migrations, $pretend = false)
    {
        // First we will just make sure that there are any migrations to run.
        // If there aren't, we will just make a note of it to the developer so
        // they're aware that all of the migrations have been run against this
        // database system.
        if (count($migrations) == 0) {
            $this->note('<info>Nothing to migrate.</info>');

            return;
        }

        $batch = $this->repository->getNextBatchNumber();

        // Once we have the array of migrations, we will spin through them and
        // run the migrations "up" so the changes are made to the databases.
        // We'll then log that the migration was run so we don't repeat it next
        // time we execute.
        foreach ($migrations as $name) {
            $this->runUp($name, $batch, $pretend);
        }
    }

    /**
     * Run "up" a migration instance.
     *
     * @param string $name
     * @param int $batch
     * @param bool $pretend
     *
     */
    protected function runUp($name, $batch, $pretend)
    {
        // First we will resolve a "real" instance of the migration class from
        // this migration name. Once we have the instances we can run the
        // actual command such as "up" or "down", or we can just simulate the
        // action.
        $migration = $this->resolve($name);

        if ($pretend) {
            $this->pretendToRun($migration, 'up');

            return;
        }

        $migration->up();

        // Once we have run a migrations class, we will log that it was run in
        // this repository so that we don't try to run it next time we do a
        // migration in the application. A migration repository keeps the
        // migrate order.
        $this->repository->log($name, $batch);

        $this->note("<info>Migrated:</info> $name");
    }

    /**
     * Rollback the last migration operation.
     *
     * @param  bool $pretend
     *
     * @return int
     */
    public function rollback($pretend = false)
    {
        if (!$this->repository->repositoryExists()) {
            $this->note('<info>Nothing to rollback.</info>');

            return 0;
        }

        $this->notes = [];

        $this->batch->validate();
        $defined = array_reverse($this->batch->getExpanded());

        // We want to pull in the last batch of migrations that ran on the
        // previous migration operation. We'll then reverse those migrations and
        // run each of them "down" to reverse the last migration "operation"
        // which ran.
        $migrations = $this->repository->getLast();

        if (count($migrations) === 0) {
            $this->note('<info>Nothing to rollback.</info>');

            return count($migrations);
        }

        // We need to reverse these migrations so that they are "downed" in
        // reverse to what they run on "up". It lets us backtrack through the
        // migrations and properly reverse the entire database schema operation
        // that ran.
        //
        // For structured migrations, we have to do some additional magic to
        // figure out the right order in which migrations should be rolled-back
        // since a simple sort by name won't do.
        $processedMigrations = [];
        foreach ($migrations as $migration) {
            $processedMigrations[$migration->migration] = $migration;
        }

        foreach ($defined as $name) {
            if (array_key_exists($name, $processedMigrations)) {
                $this->runDown((object) $processedMigrations[$name], $pretend);
            }
        }

        return count($migrations);
    }

    /**
     * Run "down" a migration instance.
     *
     * @param object $migration
     * @param bool $pretend
     *
     */
    protected function runDown($migration, $pretend)
    {
        $name = $migration->migration;

        // First we will get the name of the migration so we can resolve
        // out an instance of the migration. Once we get an instance we can
        // either run a pretend execution of the migration or we can run the
        // real migration.
        $instance = $this->resolve($name);

        if ($pretend) {
            $this->pretendToRun($instance, 'down');

            return;
        }

        $instance->down();

        // Once we have successfully run the migration "down" we will remove it
        // from the migration repository so it will be considered to have not
        // been run by the application then will be able to fire by any later
        // operation.
        $this->repository->delete($migration);

        $this->note("<info>Rolled back:</info> $name");
    }

    /**
     * Pretend to run the migrations.
     *
     * @param object $migration
     * @param string $method
     *
     */
    protected function pretendToRun($migration, $method)
    {
        foreach ($this->getQueries($migration, $method) as $query) {
            $name = get_class($migration);

            $this->note("<info>{$name}:</info> {$query['query']}");
        }
    }

    /**
     * Get all of the queries that would be run for a migration.
     *
     * @param object $migration
     * @param string $method
     *
     * @return array
     */
    protected function getQueries($migration, $method)
    {
        $connection = $migration->getConnection();

        // Now that we have the connections we can resolve it and pretend to run
        // the queries against the database returning the array of raw SQL
        // statements that would get fired against the database system for this
        // migration.
        $db = $this->resolveConnection($connection);

        return $db->pretend(function () use ($migration, $method) {
            $migration->$method();
        });
    }

    /**
     * Resolve a migration instance.
     *
     * @param string $name
     *
     * @return object
     */
    public function resolve($name)
    {
        $class = $this->batch->resolve($name);

        return new $class();
    }

    /**
     * Raise a note event for the migrator.
     *
     * @param string $message
     *
     */
    protected function note($message)
    {
        $this->notes[] = $message;
    }

    /**
     * Get the notes for the last operation.
     *
     * @return array
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Resolve the database connection instance.
     *
     * @param string $connection
     *
     * @return Connection
     */
    public function resolveConnection($connection)
    {
        return $this->resolver->connection($connection);
    }

    /**
     * Set the default connection name.
     *
     * @param string $name
     *
     */
    public function setConnection($name)
    {
        if (!is_null($name)) {
            $this->resolver->setDefaultConnection($name);
        }

        $this->repository->setSource($name);

        $this->connection = $name;
    }

    /**
     * Get the migration repository instance.
     *
     * @return MigrationRepositoryInterface
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Determine if the migration repository exists.
     *
     * @return bool
     */
    public function repositoryExists()
    {
        return $this->repository->repositoryExists();
    }
}
