<?php namespace Sofa\Revisionable\Adapters;

use Sofa\Revisionable\UserProvider;
use Illuminate\Contracts\Auth\Guard as BaseGuard;

class Guard implements UserProvider
{
    /**
     * Auth provider instance.
     *
     * @var \Illuminate\Contracts\Auth\Guard
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
     * @param \Illuminate\Contracts\Auth\Guard $provider
     * @param string $field
     */
    public function __construct(BaseGuard $provider, $field = null)
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
        if ($user = $this->provider->user()) {
            return ($field = $this->field) ? (string) $user->{$field} : $user->getAuthIdentifier();
        }
    }
}
