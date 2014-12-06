<?php namespace Sofa\Revisionable\Laravel4;

use Illuminate\Support\ServiceProvider;
use ReflectionClass;

class RevisionableServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Boot the package.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('sofa/revisionable', 'revisionable', $this->guessPackagePath());
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('revisionable.logger', function () {
            return new \Sofa\Revisionable\Laravel4\DbLogger($this->app['db']->connection());
        });

        $this->app->bind('Sofa\Revisionable\Presenter', 'Sofa\Revisionable\Laravel4\Presenter');

        $this->app->bind('Sofa\Revisionable\Listener', 'Sofa\Revisionable\Laravel4\Listener');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('revisionable.logger');
    }

    /**
     * Guess real path of the package.
     *
     * @return string
     */
    public function guessPackagePath()
    {
        $path = (new ReflectionClass($this))->getFileName();

        return realpath(dirname($path).'/../../');
    }
}
