<?php

namespace Sofa\Revisionable\Laravel;

use Illuminate\Console\Command;
use Illuminate\Support\Composer;
use Illuminate\Filesystem\Filesystem;

class RevisionsTableCommand extends Command
{
    /** @var string */
    protected $name = 'revisions:table';

    /** @var string */
    protected $description = 'Create a migration for the revisions database table';

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
    public function handle()
    {
        $path = $this->createBaseMigration();
        $this->files->put($path, $this->files->get(__DIR__.'/../migrations/revisions.stub'));
        $this->info('Revisions migration created successfully!');
        $this->composer->dumpAutoloads();
    }

    /**
     * Create a base migration file for the table.
     *
     * @return string
     */
    protected function createBaseMigration()
    {
        $name = 'create_sofa_revisions_table';

        $path = $this->laravel->databasePath().'/migrations';

        return $this->laravel['migration.creator']->create($name, $path);
    }
}
