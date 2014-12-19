<?php namespace Sofa\Revisionable\Tests\Laravel4;

use Sofa\Revisionable\Laravel4\RevisionableTrait;
use Sofa\Revisionable\Revisionable;

class RevisionableTraitStub implements Revisionable
{
    public $attributes    = ['foo' => 'foo_old', 'bar' => 'bar_new', 'baz' => 'baz_new'];
    public $original      = ['foo' => 'foo_old', 'bar' => 'bar_old', 'baz' => 'baz_old'];
    public $defaultEvents = ['Created', 'Updated', 'Deleted', 'Restored'];

    use RevisionableTrait {
        getRevisionableEvents as getEvents;
        registerCreatedListener as registerCreated;
        registerUpdatedListener as registerUpdated;
        registerDeletedListener as registerDeleted;
        registerRestoredListener as registerRestored;
        bootLogger as orgBootLogger;
    }

    public static function getRevisionableEvents()
    {
        return static::getEvents();
    }

    public function registerCreatedListener()
    {
        return static::registerCreated();
    }

    public function registerUpdatedListener()
    {
        return static::registerUpdated();
    }

    public function registerDeletedListener()
    {
        return static::registerDeleted();
    }

    public function registerRestoredListener()
    {
        return static::registerRestored();
    }

    public function setRevisionable($attributes)
    {
        $this->revisionable = $attributes;
    }

    public static function bootLogger()
    {

    }

    public static function created($callback)
    {

    }

    public static function updated($callback)
    {

    }

    public static function deleted($callback)
    {

    }

    public static function restored($callback)
    {

    }
}
