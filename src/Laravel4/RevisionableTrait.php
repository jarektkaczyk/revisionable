<?php namespace Sofa\Revisionable\Laravel4;

use \App;

/**
 * @property int   revisionsCount
 * @property array relations
 * @property array original
 * @property array attributes
 * @property array attributes
 * @property array revisionableConnection
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
        if ( ! static::$revisionableLogger) {
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

        return array_map(function ($attribute) {
            return (string) $attribute;
        }, $attributes);
    }

    /**
     * Get an array of current revisionable attributes.
     *
     * @return array
     */
    public function getNewAttributes()
    {
        $attributes = $this->getRevisionableItems($this->attributes);

        return array_map(function ($attribute) {
            return (string) $attribute;
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
        return $this->hasMany('Sofa\Revisionable\Laravel4\Revision', 'row_id')
            ->where('table_name', $this->getTable());
    }

    /**
     * Revisionable has one Revision count.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function revisionsCount()
    {
        return $this->hasOne('Sofa\Revisionable\Laravel4\Revision', 'row_id')
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
        if ( ! array_key_exists('revisionsCount', $this->relations)) {
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
     * User has one FirstRevision
     *
     * @return Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function oldestRevision()
    {
        return $this->hasOne('Sofa\Revisionable\Laravel4\Revision', 'row_id')
            ->where('table_name', $this->getTable())
            ->oldest();
    }

    abstract public static function created();
    abstract public static function updated();
    abstract public static function deleted();
    abstract public static function restored();
    abstract public function getTable();
    abstract public function hasMany();
    abstract public function hasOne();
    abstract public function load();
    abstract public function getRelation();
}
