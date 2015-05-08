<?php namespace Sofa\Revisionable\Laravel;

use Illuminate\Support\ServiceProvider;
use ReflectionClass;

class FourServiceProvider extends ServiceProvider
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

        $this->bootModel();
    }

    /**
     * Bind Revisionable logger implementation to the IoC.
     *
     * @return void
     */
    protected function bindLogger()
    {
        $table = $this->app['config']->get('revisionable::config.table', 'revisions');

        $this->app->bindShared('revisionable.logger', function ($app) use ($table) {
            return new \Sofa\Revisionable\Laravel\DbLogger($app['db']->connection(), $table);
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

            case 'sentinel':
                $this->bindSentinelProvider();
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
            $field = $app['config']->get('revisionable::config.userfield');

            return new \Sofa\Revisionable\Adapters\Sentry($app['sentry'], $field);
        });
    }

    /**
     * Bind adapter for Sentinel to the IoC.
     *
     * @return void
     */
    protected function bindSentinelProvider()
    {
        $this->app->bindShared('revisionable.userprovider', function ($app) {
            $field = $app['config']->get('revisionable::config.userfield');

            return new \Sofa\Revisionable\Adapters\Sentinel($app['sentinel'], $field);
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
            $field = $app['config']->get('revisionable::config.userfield');

            return new \Sofa\Revisionable\Adapters\IlluminateAuth($app['auth']->driver(), $field);
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
            return new \Sofa\Revisionable\Laravel\Listener($app['revisionable.userprovider']);
        });
    }

    /**
     * Bind presenter implementation to the IoC.
     *
     * @return void
     */
    protected function bindPresenter()
    {
        $this->app->bind('Sofa\Revisionable\Presenter', function ($app, $parameters) {
            $revision  = reset($parameters) ?: new Revision;

            $templates = $app['config']->get('revisionable::config.templates', []);

            return new \Sofa\Revisionable\Laravel\Presenter($revision, $templates);
        });
    }

    /**
     * Boot the Revision model.
     *
     * @return void
     */
    protected function bootModel()
    {
        $table = $this->app['config']->get('revisionable::config.table', 'revisions');

        forward_static_call_array(['\Sofa\Revisionable\Laravel\Revision', 'setCustomTable'], [$table]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
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
