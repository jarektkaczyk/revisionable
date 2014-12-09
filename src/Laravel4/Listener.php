<?php namespace Sofa\Revisionable\Laravel4;

use Sofa\Revisionable\Listener as ListenerInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\UserInterface;

class Listener implements ListenerInterface
{
    /**
     * Auth manager instance.
     *
     * @var mixed
     */
    protected $auth;
    
    /**
     * Create new listener.
     *
     * @param mixed $auth
     */
    public function __construct($auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle created event.
     *
     * @param  mixed
     * @return null|bool
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
     * @return null|bool
     */
    public function onUpdated($model)
    {
        $this->checkModel($model);

        if ( ! $model->isRevisioned() || empty($model->getDiff())) {
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
     * @return null|bool
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
     * @return null|bool
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
     * @return \Illuminate\Auth\UserInterface|null
     */
    public function getCurrentUser()
    {
        return $this->auth->getUser();
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
