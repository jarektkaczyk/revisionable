<?php

namespace Sofa\Revisionable\Laravel;

use Illuminate\Console\Command;
use Illuminate\Foundation\Composer;
use Illuminate\Filesystem\Filesystem;

class RevisionsUpgradeCommand extends Command
{
    /** @var string */
    protected $name = 'revisions:upgrade-2.1';

    /** @var string */
    protected $description = 'Create a migration upgrading to v2.1';

    /** @var \Illuminate\Filesystem\Filesystem */
    protected $files;

    /** @var \Illuminate\Foundation\Composer */
    protected $composer;

    /**
     * @param \Illuminate\Filesystem\Filesystem $files
     * @param \Illuminate\Foundation\Composer   $composer
     */
    public function __construct(Filesystem $files, Composer $composer)
    {
        parent::__construct();
        $this->files = $files;
        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     */
    public function fire()
    {
        $path = $this->createBaseMigration();
        $this->files->put($path, $this->files->get(__DIR__.'/../migrations/upgrade-2.1.stub'));
        $this->info('Revisions upgrade migration created successfully!');
        $this->composer->dumpAutoloads();
    }

    /**
     * Create a base migration file for the table.
     *
     * @return string
     */
    protected function createBaseMigration()
    {
        $name = 'add_morph_relation_to_revisions';

        $path = $this->laravel->databasePath().'/migrations';

        return $this->laravel['migration.creator']->create($name, $path);
    }
}
