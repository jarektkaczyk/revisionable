<?php namespace Sofa\Revisionable\Laravel4;

use Sofa\Revisionable\Listener as ListenerInterface;
use Illuminate\Database\Eloquent\Model;
use \Auth;

class Listener implements ListenerInterface
{
    /**
     * Handle created event.
     *
     * @param  mixed
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function onCreated($model)
    {
        if ( ! $model instanceof Model) {
            throw new \InvalidArgumentException(
                '$model must be of type Illuminate\Database\Eloquent\Model. '
                . get_class($model) . ' given.'
            );
        }

        $type  = 'create';
        $table = $model->getTable();
        $id    = $model->getKey();
        $old   = [];
        $new   = $model->getNewAttributes();
        $user  = Auth::getUser();

        $model::$revisionableLogger
            ->on($model->getConnection())
            ->revisionLog($type, $table, $id, $old, $new, $user);
    }

    /**
     * Handle updated event.
     *
     * @param  mixed
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function onUpdated($model)
    {
        if ( ! $model instanceof Model) {
            throw new \InvalidArgumentException(
                '$model must be of type Illuminate\Database\Eloquent\Model. '
                . get_class($model) . ' given.'
            );
        }

        if (empty($model->getDiff())) {
            return;
        }

        $type  = 'update';
        $table = $model->getTable();
        $id    = $model->getKey();
        $old   = $model->getOldAttributes();
        $new   = $model->getNewAttributes();
        $user  = Auth::getUser();

        $model::$revisionableLogger
            ->on($model->getConnection())
            ->revisionLog($type, $table, $id, $old, $new, $user);
    }

    /**
     * Handle deleted event.
     *
     * @param  mixed
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function onDeleted($model)
    {
        if ( ! $model instanceof Model) {
            throw new \InvalidArgumentException(
                '$model must be of type Illuminate\Database\Eloquent\Model. '
                . get_class($model) . ' given.'
            );
        }

        $type  = 'delete';
        $table = $model->getTable();
        $id    = $model->getKey();
        $old   = [];
        $new   = [];
        $user  = Auth::getUser();

        $model::$revisionableLogger
            ->on($model->getConnection())
            ->revisionLog($type, $table, $id, $old, $new, $user);
    }

    /**
     * Handle restored event.
     *
     * @param  mixed
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function onRestored($model)
    {
        if ( ! $model instanceof Model) {
            throw new \InvalidArgumentException(
                '$model must be of type Illuminate\Database\Eloquent\Model. '
                . get_class($model) . ' given.'
            );
        }

        $type  = 'restore';
        $table = $model->getTable();
        $id    = $model->getKey();
        $old   = [];
        $new   = [];
        $user  = Auth::getUser();

        $model::$revisionableLogger
            ->on($model->getConnection())
            ->revisionLog($type, $table, $id, $old, $new, $user);
    }
}
