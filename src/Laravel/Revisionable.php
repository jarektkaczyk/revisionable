<?php

namespace Sofa\Revisionable\Laravel;

use Carbon\Carbon;

trait Revisionable
{
    /**
     * Boot the trait for a model.
     *
     * @return void
     */
    protected static function bootRevisionable()
    {
        static::observe(Listener::class);
    }

    /**
     * Get record version at given timestamp.
     *
     * @param  \DateTime|string $timestamp  DateTime|Carbon object or parsable date string @see strtotime()
     * @return \Sofa\Revisionable\Laravel\Revision|\Sofa\Revisionable\laravel\Presenter|null
     */
    public function snapshot($timestamp)
    {
        $revision = $this->revisions()
                         ->where('created_at', '<=', Carbon::parse($timestamp))
                         ->first();

        return ($revision) ? $this->wrapRevision($revision) : null;
    }

    /**
     * Get record version at given step back in history.
     *
     * @param  integer $step
     * @return \Sofa\Revisionable\Laravel\Revision|\Sofa\Revisionable\laravel\Presenter|null
     */
    public function historyStep($step)
    {
        $revision = $this->revisions()->skip($step)->first();

        return ($revision) ? $this->wrapRevision($revision) : null;
    }

    /**
     * Determine if model has history at given timestamp if provided or any at all.
     *
     * @param  \DateTime|string $timestamp  DateTime|Carbon object or parsable date string @see strtotime()
     * @return boolean
     */
    public function hasHistory($timestamp = null)
    {
        if ($timestamp) {
            return (bool) $this->snapshot($timestamp);
        }

        return $this->revisions()->exists();
    }

    /**
     * Get an array of updated revisionable attributes.
     *
     * @return array
     */
    public function getDiff()
    {
        return array_diff_assoc($this->getNewAttributes(), $this->getOldAttributes());
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
    protected function getRevisionableItems(array $values)
    {
        if (count($this->getRevisionable()) > 0) {
            return array_intersect_key($values, array_flip($this->getRevisionable()));
        }

        return array_diff_key($values, array_flip($this->getNonRevisionable()));
    }

    /**
     * Attributes being revisioned.
     *
     * @var array
     */
    public function getRevisionable()
    {
        return property_exists($this, 'revisionable') ? (array) $this->revisionable : [];
    }

    /**
     * Attributes hidden from revisioning if revisionable are not provided.
     *
     * @var array
     */
    public function getNonRevisionable()
    {
        return property_exists($this, 'nonRevisionable')
                ? (array) $this->nonRevisionable
                : ['created_at', 'updated_at', 'deleted_at'];
    }

    /**
     * Model has many Revisions
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function revisions()
    {
        return $this->hasMany(Revision::class, 'row_id')->for($this)->ordered();
    }

    /**
     * Model has one latestRevision
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function latestRevision()
    {
        return $this->hasOne(Revision::class, 'row_id')->for($this)->ordered();
    }

    /**
     * Accessor for revisions property
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRevisionsAttribute()
    {
        if (!$this->relationLoaded('revisions')) {
            $this->load('revisions');
        }

        return $this->wrapRevision($this->getRelation('revisions'));
    }

    /**
     * Accessor for latestRevision attribute
     * @link https://laravel.com/docs/eloquent-mutators#accessors-and-mutators
     *
     * @return \Sofa\Revisionable\Laravel\Presenter|\Sofa\Revisionable\Laravel\Revision
     */
    public function getLatestRevisionAttribute()
    {
        if (!$this->relationLoaded('latestRevision')) {
            $this->load('latestRevision');
        }

        return $this->wrapRevision($this->getRelation('latestRevision'));
    }

    /**
     * Wrap revision model with the presenter if provided.
     *
     * @param  \Sofa\Revisionable\Laravel\Revision|\Illuminate\Database\Eloquent\Collection $history
     * @return \Sofa\Revisionable\Laravel\Presenter|\Sofa\Revisionable\Laravel\Revision
     */
    public function wrapRevision($history)
    {
        return $presenter = $this->getRevisionPresenter()
                ? $presenter::make($history, $this)
                : $history;
    }

    /**
     * Get revision presenter class for the model.
     *
     * @return string|null
     */
    public function getRevisionPresenter()
    {
        if (!property_exists($this->revisionPresenter)) {
            return;
        }

        return class_exists($this->revisionPresenter)
                ? $this->revisionPresenter
                : Presenter::class;
    }
}
