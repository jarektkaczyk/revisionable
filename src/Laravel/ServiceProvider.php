<?php

namespace Sofa\Revisionable\Laravel;

use Sofa\Revisionable\Logger;
use Sofa\Revisionable\Adapters;
use Sofa\Revisionable\UserProvider;

/**
 * @method void publishes(array $paths, $group = null)
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        $path = realpath(__DIR__.'/..');

        $this->publishes([
            $path.'/config/config.php' => config_path('sofa_revisionable.php'),
        ]);
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->bindLogger();
        $this->bindUserProvider();
        $this->bootModel();
        $this->registerCommand();
    }

    /**
     * Bind Revisionable logger implementation to the IoC.
     */
    protected function bindLogger()
    {
        $table = $this->app['config']->get('sofa_revisionable.table', 'revisions');
        $connection = $this->app['config']->get('sofa_revisionable.connection');

        $this->app->singleton('revisionable.logger', function ($app) use ($table, $connection) {
            return new DbLogger($app['db']->connection($connection), $table);
        });
        $this->app->alias('revisionable.logger', Logger::class);
    }

    /**
     * Bind user provider implementation to the IoC.
     */
    protected function bindUserProvider()
    {
        $userProvider = $this->app['config']->get('sofa_revisionable.userprovider');

        switch ($userProvider) {
            case 'sentry':
                $this->bindSentryProvider();
                break;

            case 'sentinel':
                $this->bindSentinelProvider();
                break;

            case 'jwt-auth':
                $this->bindJwtAuthProvider();
                break;

            default:
                $this->bindGuardProvider();
        }
        $this->app->alias('revisionable.userprovider', UserProvider::class);
    }

    /**
     * Bind adapter for Sentry to the IoC.
     */
    protected function bindSentryProvider()
    {
        $this->app->singleton('revisionable.userprovider', function ($app) {
            $field = $app['config']->get('sofa_revisionable.userfield');

            return new Adapters\Sentry($app['sentry'], $field);
        });
    }

    /**
     * Bind adapter for Sentinel to the IoC.
     */
    protected function bindSentinelProvider()
    {
        $this->app->singleton('revisionable.userprovider', function ($app) {
            $field = $app['config']->get('sofa_revisionable.userfield');

            return new Adapters\Sentinel($app['sentinel'], $field);
        });
    }

    /**
     * Bind adapter for JWT Auth to the IoC.
     */
    private function bindJwtAuthProvider()
    {
        $this->app->singleton('revisionable.userprovider', function ($app) {
            $field = $app['config']->get('sofa_revisionable.userfield');

            return new Adapters\JwtAuth($app['tymon.jwt.auth'], $field);
        });
    }

    /**
     * Bind adapter for Illuminate Guard to the IoC.
     */
    protected function bindGuardProvider()
    {
        $this->app->singleton('revisionable.userprovider', function ($app) {
            $field = $app['config']->get('sofa_revisionable.userfield');

            return new Adapters\Guard($app['auth']->driver(), $field);
        });
    }

    /**
     * Boot the Revision model.
     */
    protected function bootModel()
    {
        $table = $this->app['config']->get('sofa_revisionable.table', 'revisions');
        $user = $this->app['config']->get('sofa_revisionable.usermodel', 'App\User');

        forward_static_call_array([Revision::class, 'setCustomTable'], [$table]);
        forward_static_call_array([Revision::class, 'setUserModel'], [$user]);
    }

    /**
     * Register revisions migration generator command.
     */
    protected function registerCommand()
    {
        $this->app->singleton('revisions.migration', function ($app) {
            return new RevisionsTableCommand($app['files'], $app['composer']);
        });

        $this->commands('revisions.migration');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            'revisionable.userprovider',
            'revisionable.logger',
            'revisions.migration',
        ];
    }
}
