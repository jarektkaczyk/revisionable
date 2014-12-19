<?php namespace Sofa\Revisionable\Laravel4;

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
     * @param  mixed
     * @return false|null
     */
    public function onCreated($model)
    {
        $this->checkModel($model);

        if ( ! $model->isRevisioned()) {
            return false;
        }

        $type  = 'create';
        $table = $model->getTable();
        $id    = $model->getKey();
        $old   = [];
        $new   = $model->getNewAttributes();
        $user  = $this->getCurrentUser();

        $logger = $model->getRevisionableLogger();

        $connection = $model->getRevisionableConnection();

        if ($connection) {
            $logger->on($connection);
        }

        $logger->revisionLog($type, $table, $id, $old, $new, $user);
    }

    /**
     * Handle updated event.
     *
     * @param  mixed
     * @return false|null
     */
    public function onUpdated($model)
    {
        $this->checkModel($model);

        if ( ! $model->isRevisioned() || ! count($model->getDiff())) {
            return false;
        }

        $type  = 'update';
        $table = $model->getTable();
        $id    = $model->getKey();
        $old   = $model->getOldAttributes();
        $new   = $model->getNewAttributes();
        $user  = $this->getCurrentUser();

        $logger = $model->getRevisionableLogger();

        $connection = $model->getRevisionableConnection();

        if ($connection) {
            $logger->on($connection);
        }

        $logger->revisionLog($type, $table, $id, $old, $new, $user);
    }

    /**
     * Handle deleted event.
     *
     * @param  mixed
     * @return false|null
     */
    public function onDeleted($model)
    {
        $this->checkModel($model);

        if ( ! $model->isRevisioned()) {
            return false;
        }

        $type  = 'delete';
        $table = $model->getTable();
        $id    = $model->getKey();
        $old   = [];
        $new   = [];
        $user  = $this->getCurrentUser();

        $logger = $model->getRevisionableLogger();

        $connection = $model->getRevisionableConnection();

        if ($connection) {
            $logger->on($connection);
        }

        $logger->revisionLog($type, $table, $id, $old, $new, $user);
    }

    /**
     * Handle restored event.
     *
     * @param  mixed
     * @return false|null
     */
    public function onRestored($model)
    {
        $this->checkModel($model);

        if ( ! $model->isRevisioned()) {
            return false;
        }

        $type  = 'restore';
        $table = $model->getTable();
        $id    = $model->getKey();
        $old   = [];
        $new   = [];
        $user  = $this->getCurrentUser();

        $logger = $model->getRevisionableLogger();

        $connection = $model->getRevisionableConnection();

        if ($connection) {
            $logger->on($connection);
        }

        $logger->revisionLog($type, $table, $id, $old, $new, $user);
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
