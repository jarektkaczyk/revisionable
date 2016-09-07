<?php

namespace Sofa\Revisionable\Adapters;

use Sofa\Revisionable\UserProvider;
use Illuminate\Http\Request;

class Session implements UserProvider
{
    /**
     * Auth provider instance.
     *
     * @var \Illuminate\Session\SessionManager
     */
    protected $provider;

    /**
     * Field from the user to be saved as author of the action.
     *
     * @var string
     */
    protected $field;

    /**
     * Create adapter instance for Global Session
     *
     * @param \Illuminate\Session\SessionManager $provider
     * @param string                 $field
     */

    public function __construct(\Illuminate\Session\SessionManager $provider, $field = null)
    {

        $this->provider = $provider;
        $this->field = $field;
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
        if ($user = $this->provider) {
            return ($field = $this->field) ? (string) $user->get($field)  : $user->get('id');
        }
    }
}
