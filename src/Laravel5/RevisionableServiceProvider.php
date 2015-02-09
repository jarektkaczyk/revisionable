<?php namespace Sofa\Revisionable\Laravel5;

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
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            $this->guessPackagePath() . '/config/config.php' => config_path('sofa_revisionable.php'),
            $this->guessPackagePath() . '/migrations/' => base_path('/database/migrations'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
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
        $this->app->bindShared('revisionable.logger', function ($app) {
            return new \Sofa\Revisionable\Laravel5\DbLogger($app['db']->connection());
        });
    }

    /**
     * Bind user provider implementation to the IoC.
     *
     * @return void
     */
    protected function bindUserProvider()
    {
        $userProvider = $this->app['config']->get('sofa_revisionable.userprovider');

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
            return new \Sofa\Revisionable\Laravel5\Listener($app['revisionable.userprovider']);
        });
    }

    /**
     * Bind presenter implementation to the IoC.
     *
     * @return void
     */
    protected function bindPresenter()
    {
        $this->app->bind('Sofa\Revisionable\Presenter', 'Sofa\Revisionable\Laravel5\Presenter');
    }

    /**
     * Boot the Revision model.
     *
     * @return void
     */
    protected function bootModel()
    {
        $table = $this->app['config']->get('sofa_revisionable.table', 'revisions');

        forward_static_call_array(['\Sofa\Revisionable\Laravel5\Revision', 'setCustomTable'], [$table]);
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
