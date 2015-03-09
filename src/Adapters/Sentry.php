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
     * Field from the user to be saved as author of the action.
     *
     * @var string
     */
    protected $field;
    
    /**
     * Create adapter instance for Sentry.
     *
     * @param SentryProvider $provider
     * @param string $field
     */
    public function __construct(SentryProvider $provider, $field = null)
    {
        $this->provider = $provider;
        $this->field    = $field;
    }

    /**
     * Get identifier of the currently logged in user.
     *
     * @return string|null
     */
    public function getUser()
    {
        return $this->getUserFieldValue();
    }

    /**
     * Get value from the user to be saved as the author.
     *
     * @return string|null
     */
    protected function getUserFieldValue()
    {
        if ($user = $this->provider->getUser()) {
            return ($field = $this->field) ? (string) $user->{$field} : $user->getLogin();
        }
    }
}
