<?php

namespace Sofa\Revisionable\Laravel;

use Sofa\Revisionable\Logger;
use Sofa\Revisionable\Adapters;
use Sofa\Revisionable\Laravel\DbLogger;
use Sofa\Revisionable\Laravel\Revision;

/**
 * @method void publishes(array $paths, $group = null)
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $path = realpath(__DIR__.'/..');

        $this->publishes([
            $path.'/config/config.php' => config_path('sofa_revisionable.php'),
            $path.'/migrations/' => base_path('/database/migrations'),
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
        $this->bootModel();
    }

    /**
     * Bind Revisionable logger implementation to the IoC.
     *
     * @return void
     */
    protected function bindLogger()
    {
        $table = $this->app['config']->get('sofa_revisionable.table', 'revisions');
        $connection = $this->app['config']->get('sofa_revisionable.connection');

        $this->app->singleton('revisionable.logger', function ($app) use ($table, $connection) {
            return new DbLogger($app['db']->connection($connection), $table);
        });
        $this->alias('revisionable.logger', Logger::class);
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

            case 'sentinel':
                $this->bindSentinelProvider();
                break;

            case 'jwt-auth':
                $this->bindJwtAuthProvider();
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
        $this->app->singleton('revisionable.userprovider', function ($app) {
            $field = $app['config']->get('sofa_revisionable.userfield');

            return new Adapters\Sentry($app['sentry'], $field);
        });
    }

    /**
     * Bind adapter for Sentinel to the IoC.
     *
     * @return void
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
     *
     * @return void
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
     *
     * @return void
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
     *
     * @return void
     */
    protected function bootModel()
    {
        $table = $this->app['config']->get('sofa_revisionable.table', 'revisions');
        $user = $this->app['config']->get('sofa_revisionable.usermodel', 'App\User');

        forward_static_call_array([Revision::class, 'setCustomTable'], [$table]);
        forward_static_call_array([Revision::class, 'setUserModel'], [$user]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return ['revisionable.userprovider', 'revisionable.logger'];
    }
}
