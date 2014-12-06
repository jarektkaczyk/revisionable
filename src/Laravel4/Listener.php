<?php namespace Sofa\Revisionable\Laravel4;

use Sofa\Revisionable\Listener as ListenerInterface;
use Illuminate\Databse\Eloquent\Model;
use \Auth;

class Listener implements ListenerInterface
{
    /**
     * Handle created event.
     *
     * @param  mixed
     * @return void
     */
    public function onCreated(Model $model)
    {
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
     */
    public function onUpdated(Model $model)
    {
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
     */
    public function onDeleted(Model $model)
    {
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
     */
    public function onRestored(Model $model)
    {
        if (method_exists($model, 'restored')) {
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
}
