<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Database\Console\Migrations;

use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Support\Composer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class MigrateMakeCommand.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Database\Console\Migrations
 */
class MigrateMakeCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new migration file';

    /**
     * The migration creator instance.
     *
     * @var \Illuminate\Database\Migrations\MigrationCreator
     */
    protected $creator;

    /**
     * @var Composer
     */
    protected $composer;

    /**
     * Create a new migration install command instance.
     *
     * @param MigrationCreator $creator
     * @param Composer $composer
     */
    public function __construct(MigrationCreator $creator, Composer $composer)
    {
        parent::__construct();

        $this->creator = $creator;
        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     */
    public function fire()
    {
        // It's possible for the developer to specify the tables to modify in
        // this schema operation. The developer may also specify if this table
        // needs to be freshly created so we can create the appropriate
        // migrations.
        $name = $this->input->getArgument('name');

        $table = $this->input->getOption('table');

        $create = $this->input->getOption('create');

        if (!$table && is_string($create)) {
            $table = $create;
        }

        // Now we are ready to write the migration out to disk. Once we've
        // written the migration out, we will dump-autoload for the entire
        // framework to make sure that the migrations are registered by the
        // class loaders.
        $this->writeMigration($name, $table, $create);

        $this->composer->dumpAutoloads();
    }

    /**
     * Write the migration file to disk.
     *
     * @param  string $name
     * @param  string $table
     * @param  bool $create
     *
     * @return string
     */
    protected function writeMigration($name, $table, $create)
    {
        $path = $this->getMigrationPath();

        $file = pathinfo(
            $this->creator->create($name, $path, $table, $create),
            PATHINFO_FILENAME
        );

        $this->line("<info>Created Migration:</info> $file");
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the migration'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            [
                'create',
                null,
                InputOption::VALUE_OPTIONAL,
                'The table to be created.',
            ],

            [
                'table',
                null,
                InputOption::VALUE_OPTIONAL,
                'The table to migrate.',
            ],
        ];
    }
}
