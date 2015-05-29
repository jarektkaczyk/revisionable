<?php namespace Sofa\Revisionable\Laravel;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Sofa\Revisionable\Revisionable;

class Presenter
{
    /**
     * Revisionable fields labels.
     *
     * @var array
     */
    protected $labels = [];

    /**
     * Revision model.
     *
     * @var \Sofa\Revisionable\Laravel\Revision
     */
    protected $revision;

    /**
     * Revisoned model.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $revisioned;

    /**
     * Revisionable fields translated to real data. For example
     * show related model's property instead of its raw id.
     *
     * @var array
     */
    protected $passThrough = [];

    /**
     * Translate revision actions.
     *
     * @var array
     */
    protected $actions = [
        'created'  => 'created',
        'updated'  => 'updated',
        'deleted'  => 'deleted',
        'restored' => 'restored',
    ];

    /**
     * Old version of revisioned model.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $oldVersion;

    /**
     * New version of revisioned model.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $newVersion;

    /**
     * HTML templates.
     *
     * @var array
     */
    protected $templates = [];

    /**
     * Create a new revision presenter.
     *
     * @param \Sofa\Revisionable\Revision $revision
     * @param \Illuminat\Database\Eloquent\Model $revisioned
     */
    public function __construct(Revision $revision, Model $revisioned)
    {
        $this->revision   = $revision;
        $this->revisioned = $revisioned;
    }

    /**
     * Present action field.
     *
     * @return string
     */
    public function action()
    {
        $action = $this->revision->action;

        return array_get($this->actions, $action, $action);
    }

    /**
     * Get custom label for revisioned field.
     *
     * @param  string $key
     * @return string
     */
    public function label($key)
    {
        return array_get($this->labels, $key, $key);
    }

    /**
     * Get value from the revision.
     *
     * @param  string $version
     * @param  string $key
     * @return mixed
     */
    public function getFromRevision($version, $key)
    {
        return ($this->isPassedThrough($key))
            ? $this->passThrough($version, $key)
            : array_get($this->{$version}, $key);
    }

    /**
     * Determine whether the value should be fetched from the relation.
     *
     * @param  string $key
     * @return boolean
     */
    protected function isPassedThrough($key)
    {
        return array_key_exists($key, $this->passThrough);
    }

    /**
     * Get value from the relation.
     *
     * @param  string $version
     * @param  string $key
     * @return mixed
     */
    protected function passThrough($version, $key)
    {
        $revisioned = $this->getVersion($version);

        $needle = $this->passThrough[$key];

        return $this->dataGet($revisioned, $needle);
    }

    /**
     * Get pass through value using dot notation.
     *
     * @param  mixed  $target
     * @param  string $key
     * @return mixed
     */
    protected function dataGet($target, $key)
    {
        foreach (explode('.', $key) as $segment) {
            if ($target instanceof Revisionable) {
                $target = $this->passThroughRevisionable($target, $segment);

            } elseif ($target instanceof Presenter || $target instanceof Revision) {
                $target = $this->passThroughRevision($target, $segment);

            } elseif ($target instanceof Model) {
                $target = $this->passThroughModel($target, $segment);

            } else {
                $target = null;
            }

            if (!$target) {
                return;
            }
        }

        return $target;
    }

    protected function passThroughRevisionable(Revisionable $revisionable, $key)
    {
        // Determine whether the model existed at the time of revision.
        if ($revisionable->created_at > $this->created_at) {
            return;
        }

        $target = $revisionable->{$key};

        // If we are working with related revisionable model then
        // return its version at the time of current revision.
        if ($target instanceof Revisionable) {
            return ($target->revisionSnapshot($this->created_at)) ?: $target;
        }

        return $target;
    }

    /**
     * Get pass through value from another revision.
     *
     * @param  \Sofa\Revisionable\Revision|\Sofa\Revisionable\Laravel\Presenter $revision
     * @param  string $key
     * @return mixed
     */
    protected function passThroughRevision($revision, $key)
    {
        $action = $revision->getAttribute('action');

        // @todo what about restored???
        if (in_array($action, ['created', 'updated'])) {
            return $revision->new($key);
        }
    }

    /**
     * Get pass through value from the Eloquent model.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  string $key
     * @return mixed
     */
    protected function passThroughModel(Model $model, $key)
    {
        return $model->{$key};
    }

    /**
     * Get revisioned model with appropriate attributes.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function getVersion($version)
    {
        if (!$this->{$version.'Version'}) {
            $revisioned = get_class($this->revisioned);

            $revision = new $revisioned;
            $revision->setRawAttributes($this->{$version});

            $this->{$version.'Version'} = $revision;
        }

        return $this->{$version.'Version'};
    }

    /**
     * Decorate revision model or array/collection of models.
     *
     * @param  mixed $revision
     * @param  \Illuminate\Database\Eloquent\Model $revisioned
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public static function make($revision, $revisioned)
    {
        if (is_array($revision)) {
            return static::makeArray($revision, $revisioned);
        }

        if ($revision instanceof Collection) {
            return static::makeCollection($revision, $revisioned);
        }

        if (! $revision || $revision instanceof Model) {
            return static::makeOne($revision, $revisioned);
        }

        throw new \InvalidArgumentException(
            'Presenter::make accepts array, collection or single resource, '.gettype($revision).' given.'
        );
    }

    /**
     * Decorate Eloquent model.
     *
     * @param  \Illuminate\Database\Eloquent\Model|null $revision
     * @param  \Illuminate\Database\Eloquent\Model $revisioned
     * @return static
     */
    public static function makeOne(Model $revision, Model $revisioned)
    {
        return new static($revision, $revisioned);
    }

    /**
     * Decorate array of Eloquent models.
     *
     * @param  array $revisions
     * @param  \Illuminate\Database\Eloquent\Model $revisioned
     * @return array
     */
    public static function makeArray(array $revisions, Model $revisioned)
    {
        return array_map(static::getMapCallback($revisioned), $revisions);
    }

    /**
     * Decorate collection of models.
     *
     * @param  \Illuminate\Database\Eloquent\Collection $revisions
     * @param  \Illuminate\Database\Eloquent\Model $revisioned
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function makeCollection(Collection $revisions, Model $revisioned)
    {
        return $revisions->map(static::getMapCallback($revisioned));
    }

    /**
     * Get callback for the array map.
     *
     * @return \Closure
     */
    protected static function getMapCallback($revisioned)
    {
        // We need to pass the calling class to the closure scope
        // instead of calling new static(), since php is going
        // to instantiate it w/o late static binding (bug).
        $presenter = get_called_class();

        return function ($revision) use ($presenter, $revisioned) {
            return new $presenter($revision, $revisioned);
        };
    }

    /**
     * Handle dynamic methods calls.
     *
     * @param  string $method
     * @param  array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (in_array($method, ['new', 'old'])) {
            array_unshift($parameters, $method);

            return call_user_func_array([$this, 'getFromRevision'], $parameters);
        }

        return call_user_func_array([$this->revision, $method], $parameters);
    }

    /**
     * Pass dynamic property calls on to underlying revision model.
     *
     * @param  string $property
     * @return mixed
     */
    public function __get($property)
    {
        // Return decorated property if method is defined on this presenter.
        if (method_exists($this, $property)) {
            return $this->$property();
        }

        return $this->revision->$property;
    }
}
