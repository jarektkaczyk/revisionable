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

        $this->registerBindings();
    }

    /**
     * Register additional bindings to the IoC.
     *
     * @return void
     */
    protected function registerBindings()
    {
        $this->bindListener();
    }

    /**
     * Bind presenter implementation.
     *
     * @return void
     */
    protected function bindPresenter()
    {
        $this->app->bind('Sofa\Revisionable\Presenter', 'Sofa\Revisionable\Laravel4\Presenter');
    }

    /**
     * Bind listener implementation.
     *
     * @return void
     */
    protected function bindListener()
    {
        $authManager = $this->app['config']->get('revisionable::auth_manager');

        switch ($authManager) {
            case 'sentry':
                $this->bindSentryListener();
                break;

            default:
                $this->bindIlluminateAuthListener();
        }
    }

    /**
     * Bind listener using generic Illuminate Auth.
     *
     * @return void
     */
    protected function bindIlluminateAuthListener()
    {
        $this->app->bind('Sofa\Revisionable\Listener', function () {
            return new \Sofa\Revisionable\Laravel4\Listener($this->app['auth']);
        });
    }

    /**
     * Bind listener using Sentry package.
     *
     * @return void
     */
    protected function bindSentryListener()
    {
        $this->app->bind('Sofa\Revisionable\Listener', function () {
            return new \Sofa\Revisionable\Laravel4\Listener($this->app['sentry']);
        });
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
