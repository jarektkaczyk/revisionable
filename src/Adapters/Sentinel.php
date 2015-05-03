<?php namespace Sofa\Revisionable\Adapters;

use Sofa\Revisionable\UserProvider;
use Cartalyst\Sentinel\Sentinel as SentinelProvider;

class Sentinel implements UserProvider
{
    /**
     * Auth provider instance.
     *
     * @var \Cartalyst\Sentinel\Sentinel
     */
    protected $provider;

    /**
     * Field from the user to be saved as author of the action.
     *
     * @var string
     */
    protected $field;

    /**
     * Create adapter instance for Sentinel.
     *
     * @param SentinelProvider $provider
     * @param string $field
     */
    public function __construct(SentinelProvider $provider, $field = null)
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
