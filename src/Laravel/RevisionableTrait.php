<?php namespace Sofa\Revisionable\Laravel;

use App;
use DateTime;

/**
 * @property int   revisionsCount
 * @property array relations
 * @property array original
 * @property array attributes
 * @property array attributes
 * @property array revisionableConnection
 *
 * @method void created(\Closure|string $callback)
 * @method void updated(\Closure|string $callback)
 * @method void deleted(\Closure|string $callback)
 * @method void restored(\Closure|string $callback)
 * @method string getTable()
 * @method void load()
 * @method mixed getRelation($relation)
 * @method \Illuminate\Database\Eloquent\Relations\HasOne hasOne($related, $foreignKey = null, $localKey = null)
 * @method \Illuminate\Database\Eloquent\Relations\HasMany hasMany($related, $foreignKey = null, $localKey = null)
 */
trait RevisionableTrait
{
    /**
     * Revisionable Logger instance.
     *
     * @var \Sofa\Revisionable\Logger
     */
    protected static $revisionableLogger;

    /**
     * Revisioning switch.
     *
     * @var boolean
     */
    protected $revisioned = true;

    /**
     * Boot revisionable trait for the model.
     *
     * @return void
     */
    public static function bootRevisionableTrait()
    {
        static::bootLogger();

        static::registerListeners();
    }

    /**
     * Register event listeners.
     *
     * @return void
     */
    public static function registerListeners()
    {
        foreach (static::getRevisionableEvents() as $event) {
            static::{"register{$event}Listener"}();
        }
    }

    /**
     * Register listener for created event.
     *
     * @return void
     */
    protected static function registerCreatedListener()
    {
        static::created('Sofa\Revisionable\Listener@onCreated');
    }

    /**
     * Register listener for updated event.
     *
     * @return void
     */
    protected static function registerUpdatedListener()
    {
        static::updated('Sofa\Revisionable\Listener@onUpdated');
    }

    /**
     * Register listener for deleted event.
     *
     * @return void
     */
    protected static function registerDeletedListener()
    {
        static::deleted('Sofa\Revisionable\Listener@onDeleted');
    }

    /**
     * Register listener for restored event.
     *
     * @return void
     */
    protected static function registerRestoredListener()
    {
        if (method_exists(get_called_class(), 'restored')) {
            static::restored('Sofa\Revisionable\Listener@onRestored');
        }
    }

    /**
     * Boot Revisionable Logger.
     *
     * @return void
     */
    public static function bootLogger()
    {
        if (!static::$revisionableLogger) {
            static::setRevisionableLogger(App::make('revisionable.logger'));
        }
    }

    /**
     * Set logger instance.
     *
     * @param mixed $logger
     */
    public static function setRevisionableLogger($logger)
    {
        static::$revisionableLogger = $logger;
    }

    /**
     * Get logger instance.
     *
     * @return \Sofa\Revisionable\Logger
     */
    public static function getRevisionableLogger()
    {
        return static::$revisionableLogger;
    }

    /**
     * Get the connection for revision logs.
     *
     * @return \Illuminate\Database\ConnectionInterface
     */
    public function getRevisionableConnection()
    {
        $connection = (isset($this->revisionableConnection)) ? $this->revisionableConnection : null;

        return static::resolveConnection($connection);
    }

    /**
     * Get an array of updated revisionable attributes.
     *
     * @return array
     */
    public function getDiff()
    {
        $old = $this->getOldAttributes();

        $new = $this->getNewAttributes();

        return array_diff_assoc($new, $old);
    }

    /**
     * Get an array of original revisionable attributes.
     *
     * @return array
     */
    public function getOldAttributes()
    {
        $attributes = $this->getRevisionableItems($this->original);

        return $this->prepareAttributes($attributes);
    }

    /**
     * Get an array of current revisionable attributes.
     *
     * @return array
     */
    public function getNewAttributes()
    {
        $attributes = $this->getRevisionableItems($this->attributes);

        return $this->prepareAttributes($attributes);
    }

    /**
     * Stringify revisionable attributes.
     *
     * @param  array  $attributes
     * @return array
     */
    protected function prepareAttributes(array $attributes)
    {
        return array_map(function ($attribute) {
            return ($attribute instanceof DateTime)
                ? $this->fromDateTime($attribute)
                : (string) $attribute;
        }, $attributes);
    }

    /**
     * Get an array of revisionable attributes.
     *
     * @param  array  $values
     * @return array
     */
    public function getRevisionableItems(array $values)
    {
        if (count($this->getRevisionable()) > 0) {
            return array_intersect_key($values, array_flip($this->getRevisionable()));
        }

        return array_diff_key($values, array_flip($this->getNonRevisionable()));
    }

    /**
     * Events being tracked.
     *
     * @var array
     */
    protected static function getRevisionableEvents()
    {
        return (isset(static::$revisionableEvents))
            ? (array) static::$revisionableEvents
            : ['Created', 'Updated', 'Deleted', 'Restored'];
    }

    /**
     * Attributes being revisioned.
     *
     * @var array
     */
    public function getRevisionable()
    {
        return (isset($this->revisionable))
            ? (array) $this->revisionable
            : [];
    }

    /**
     * Attributes hidden from revisioning if revisionable are not provided.
     *
     * @var array
     */
    public function getNonRevisionable()
    {
        return (isset($this->nonRevisionable))
            ? (array) $this->nonRevisionable
            : ['created_at', 'updated_at', 'deleted_at'];
    }

    /**
     * Determine if model should be revisioned.
     *
     * @return boolean
     */
    public function isRevisioned()
    {
        return $this->revisioned;
    }

    /**
     * Disable revisioning for current instance.
     *
     * @return void
     */
    public function disableRevisioning()
    {
        $this->revisioned = false;
    }

    /**
     * Enable revisioning for current instance.
     *
     * @return void
     */
    public function enableRevisioning()
    {
        $this->revisioned = true;
    }

    /**
     * Model has many Revision
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function revisions()
    {
        return $this->hasMany('Sofa\Revisionable\Laravel\Revision', 'row_id')
            ->latest()
            ->where('table_name', $this->getTable());
    }

    /**
     * Accessor for revisions property
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRevisionsAttribute()
    {
        $this->loadRelationIfNecessary('revisions');

        $collection = $this->getRelation('revisions');

        $presenter = $this->getRevisionPresenter();

        $default = $this->getDefaultRevisionPresenter();

        if (is_subclass_of($presenter, $default) || $presenter == $default) {
            return $presenter::make($collection, $this);
        }

        return $collection;
    }

    /**
     * Get record version at given timestamp.
     *
     * @param  \Carbon\Carbon|string $timestamp
     *
     * @return \Sofa\Revisionable\Laravel\Revision|\Sofa\Revisionable\laravel\Presenter|null
     */
    public function revisionSnapshot($timestamp)
    {
        $revision = $this->revisions()->where('created_at', '<=', $timestamp)->first();

        return ($revision) ? $this->wrapRevision($revision) : null;
    }

    /**
     * Get record version at given step in history.
     *
     * @param  integer $step
     *
     * @return \Sofa\Revisionable\Laravel\Revision|\Sofa\Revisionable\laravel\Presenter|null
     */
    public function revisionStep($step)
    {
        $revision = $this->revisions()->skip($step)->first();

        return ($revision) ? $this->wrapRevision($revision) : null;
    }

    /**
     * Wrap revision model with the presenter if provided.
     *
     * @param  \Sofa\Revisionable\Laravel\Revision $revision
     * @return \Sofa\Revisionable\Laravel\Presenter|\Sofa\Revisionable\Laravel\Revision
     */
    public function wrapRevision(Revision $revision)
    {
        $presenter = $this->getRevisionPresenter();

        return (is_subclass_of($presenter, $this->getDefaultRevisionPresenter())
                || $presenter == $this->getDefaultRevisionPresenter())
                    ? $presenter::make($revision, $this)
                    : $revision;
    }

    /**
     * Load revisions relation if not loaded.
     *
     * @param  string $relation
     *
     * @return void
     */
    protected function loadRelationIfNecessary($relation)
    {
        if (!array_key_exists($relation, $this->relations)) {
            $this->load($relation);
        }
    }

    /**
     * Revisionable has one Revision count.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function revisionsCount()
    {
        return $this->hasOne('Sofa\Revisionable\Laravel\Revision', 'row_id')
            ->where('table_name', $this->getTable())
            ->selectRaw('count(*) as aggregate, row_id')
            ->groupBy('row_id');
    }

    /**
     * Convenient accessor for revisionsCount relation.
     *
     * @return integer
     */
    public function getRevisionsCountAttribute()
    {
        if (!array_key_exists('revisionsCount', $this->relations)) {
            $this->load('revisionsCount');
        }

        $relation = $this->getRelation('revisionsCount');

        return ($relation) ? (int) $relation->aggregate : 0;
    }

    /**
     * Determine if model has any revisions history.
     *
     * @return boolean
     */
    public function hasRevisions()
    {
        return (bool) $this->revisionsCount;
    }

    /**
     * Determine if model has any revisions history.
     *
     * @return boolean
     */
    public function hasHistory()
    {
        return $this->hasRevisions();
    }

    /**
     * User has one oldestRevision
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function oldestRevision()
    {
        return $this->hasOne('Sofa\Revisionable\Laravel\Revision', 'row_id')
            ->where('table_name', $this->getTable())
            ->oldest();
    }

    /**
     * Accessor for oldestRevision property
     *
     * @return \Sofa\Revisionable\Laravel\Revision|\Sofa\Revisionable\Laravel\Presenter|null
     */
    public function getOldestRevisionAttribute()
    {
        $this->loadRelationIfNecessary('oldestRevision');

        $revision = $this->getRelation('oldestRevision');

        return ($revision) ? $this->wrapRevision($revision) : null;
    }

    /**
     * User has one latestRevision
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestRevision()
    {
        return $this->hasOne('Sofa\Revisionable\Laravel\Revision', 'row_id')
            ->where('table_name', $this->getTable())
            ->latest();
    }

    /**
     * Accessor for latestRevision property
     *
     * @return \Sofa\Revisionable\Laravel\Revision|\Sofa\Revisionable\Laravel\Presenter|null
     */
    public function getLatestRevisionAttribute()
    {
        $this->loadRelationIfNecessary('latestRevision');

        $revision = $this->getRelation('latestRevision');

        return ($revision) ? $this->wrapRevision($revision) : null;
    }

    /**
     * Set revision presenter class or true for default.
     *
     * @param string|true $class
     */
    public function setRevisionPresenter($class)
    {
        $this->revisionPresenter = $class;
    }

    /**
     * Get revision presenter class for the model.
     *
     * @return string|null
     */
    public function getRevisionPresenter()
    {
        if (!isset($this->revisionPresenter)) {
            return null;
        }

        // Use default or custom presenter class if provided.
        return ($this->revisionPresenter === true)
            ? $this->getDefaultRevisionPresenter()
            : $this->revisionPresenter;
    }

    /**
     * Get default revision presenter from the package.
     *
     * @return string
     */
    public function getDefaultRevisionPresenter()
    {
        return 'Sofa\Revisionable\Laravel\Presenter';
    }
}
