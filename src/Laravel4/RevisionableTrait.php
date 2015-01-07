<?php namespace Sofa\Revisionable\Laravel4;

use \App;

/**
 * @property array original
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
        return $this->getRevisionableItems($this->original);
    }

    /**
     * Get an array of current revisionable attributes.
     *
     * @return array
     */
    public function getNewAttributes()
    {
        return $this->getRevisionableItems($this->attributes);
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
}
