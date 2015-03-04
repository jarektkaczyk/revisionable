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
     * Field from the user to be saved as author of the action.
     *
     * @var string
     */
    protected $field;
    
    /**
     * Create adapter instance for Illuminate Guard.
     *
     * @param \Illuminate\Auth\Guard $provider
     * @param string $field
     */
    public function __construct(Guard $provider, $field = null)
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
        return $this->getUserFieldValue($user);
    }

    /**
     * Get value from the user to be saved as the author.
     * 
     * @return string|null
     */
    protected function getUserFieldValue()
    {
        if ($user = $this->provider->user())
        {
            return ($field = $this->field) ? (string) $user->{$field} : $user->getAuthIdentifier();
        }
    }
}
