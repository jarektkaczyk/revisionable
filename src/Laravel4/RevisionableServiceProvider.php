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
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // We need to register package namespace now in order to use its config
        // when binding the UserProvider, since boot method is called later
        $this->package('sofa/revisionable', 'revisionable', $this->guessPackagePath());

        $this->bindLogger();

        $this->bindUserProvider();

        $this->bindListener();

        $this->bindPresenter();
    }

    /**
     * Bind Revisionable logger implementation to the IoC.
     *
     * @return void
     */
    protected function bindLogger()
    {
        $this->app->bindShared('revisionable.logger', function ($app) {
            return new \Sofa\Revisionable\Laravel4\DbLogger($app['db']->connection());
        });
    }

    /**
     * Bind user provider implementation to the IoC.
     *
     * @return void
     */
    protected function bindUserProvider()
    {
        $userProvider = $this->app['config']->get('revisionable::config.userprovider');

        switch ($userProvider) {
            case 'sentry':
                $this->bindSentryProvider();
                break;

            default:
                $this->bindGuardProvider();
        }
    }

    /**
     * Bind adapter for Sentry to the IoC.
     *
     * @return void
     */
    protected function bindSentryProvider()
    {
        $this->app->bindShared('revisionable.userprovider', function ($app) {
            return new \Sofa\Revisionable\Adapters\Sentry($app['sentry']);
        });
    }

    /**
     * Bind adapter for Illuminate Guard to the IoC.
     *
     * @return void
     */
    protected function bindGuardProvider()
    {
        $this->app->bindShared('revisionable.userprovider', function ($app) {
            return new \Sofa\Revisionable\Adapters\IlluminateAuth($app['auth']->driver());
        });
    }

    /**
     * Bind listener implementation to the Ioc.
     *
     * @return void
     */
    protected function bindListener()
    {
        $this->app->bind('Sofa\Revisionable\Listener', function ($app) {
            return new \Sofa\Revisionable\Laravel4\Listener($app['revisionable.userprovider']);
        });
    }

    /**
     * Bind presenter implementation to the IoC.
     *
     * @return void
     */
    protected function bindPresenter()
    {
        $this->app->bind('Sofa\Revisionable\Presenter', 'Sofa\Revisionable\Laravel4\Presenter');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'revisionable.logger',
            'revisionable.userprovider',
        ];
    }

    /**
     * Guess real path of the package.
     *
     * @return string
     */
    public function guessPackagePath()
    {
        $path = (new ReflectionClass($this))->getFileName();

        return realpath(dirname($path).'/../');
    }
}
