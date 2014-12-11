<?php namespace Sofa\Revisionable\Adapters;

use Sofa\Revisionable\UserProvider;
use Illuminate\Auth\Guard;

class IlluminateAuth implements UserProvider
{
    /**
     * Auth provider instance.
     *
     * @var \Illuminate\Auth\Guard
     */
    protected $provider;
    
    /**
     * Create adapter instance for Illuminate Guard.
     *
     * @param \Illuminate\Auth\Guard $provider
     */
    public function __construct(Guard $provider)
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
        return ($user = $this->provider->getUser()) ? $user->getAuthIdentifier() : null;
    }
}
