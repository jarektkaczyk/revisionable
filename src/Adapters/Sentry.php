<?php namespace Sofa\Revisionable\Adapters;

use Sofa\Revisionable\UserProvider;
use Cartalyst\Sentry\Sentry as SentryProvider;

class Sentry implements UserProvider
{
    /**
     * Auth provider instance.
     *
     * @var \Cartalyst\Sentry\Sentry
     */
    protected $provider;
    
    /**
     * Create adapter instance for Sentry.
     *
     * @param Sentry $provider
     */
    public function __construct(SentryProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Get identifier of the currently logged in user.
     *
     * @return string|null
     */
    public function getUser()
    {
        return ($user = $this->provider->getUser()) ? $user->getLogin() : null;
    }
}
