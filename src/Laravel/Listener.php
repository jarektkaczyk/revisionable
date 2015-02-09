<?php namespace Sofa\Revisionable\Laravel;

use Sofa\Revisionable\Listener as ListenerInterface;
use Sofa\Revisionable\UserProvider;
use Illuminate\Database\Eloquent\Model;

class Listener implements ListenerInterface
{
    /**
     * User provider instance.
     *
     * @var mixed
     */
    protected $userProvider;
    
    /**
     * Create new listener.
     *
     * @param UserProvider $userProvider
     */
    public function __construct(UserProvider $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    /**
     * Handle created event.
     *
     * @param  \Illuminate\Database\Eloquent\Model
     * @return false|null
     */
    public function onCreated($model)
    {
        return $this->log('create', $model);
    }

    /**
     * Handle updated event.
     *
     * @param  \Illuminate\Database\Eloquent\Model
     * @return false|null
     */
    public function onUpdated($model)
    {
        if ( ! count($model->getDiff())) {
            return false;
        }

        return $this->log('update', $model);
    }

    /**
     * Handle deleted event.
     *
     * @param  \Illuminate\Database\Eloquent\Model
     * @return false|null
     */
    public function onDeleted($model)
    {
        return $this->log('delete', $model);
    }

    /**
     * Handle restored event.
     *
     * @param  \Illuminate\Database\Eloquent\Model
     * @return false|null
     */
    public function onRestored($model)
    {
        return $this->log('restore', $model);
    }

    /**
     * Get currently logged in user.
     *
     * @return string|null
     */
    public function getCurrentUser()
    {
        return $this->userProvider->getUser();
    }

    /**
     * Log the revision.
     *
     * @param  string $type
     * @param  mixed $model
     * @return false|null
     */
    protected function log($type, $model)
    {
        $this->checkModel($model);

        if ( ! $model->isRevisioned()) {
            return false;
        }

        $table = $model->getTable();
        $id    = $model->getKey();
        $user  = $this->getCurrentUser();
        $old   = [];
        $new   = [];

        switch ($type) {
            case 'create':
                $new = $model->getNewAttributes();
                break;
            case 'update':
                $old = $model->getOldAttributes();
                $new = $model->getNewAttributes();
                break;
        }

        $logger = $model->getRevisionableLogger();

        if ($connection = $model->getRevisionableConnection()) {
            $logger->on($connection);
        }

        $logger->revisionLog($type, $table, $id, $old, $new, $user);
    }

    /**
     * Determine if provided model is valid revisionable object.
     *
     * @param  mixed
     *
     * @throws \InvalidArgumentException
     */
    protected function checkModel($model)
    {
        if ( ! $model instanceof Model) {
            throw new \InvalidArgumentException(
                '$model must be of type Illuminate\Database\Eloquent\Model. '
                . get_class($model) . ' given.'
            );
        }
    }
}
