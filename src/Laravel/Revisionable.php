<?php

namespace Sofa\Revisionable\Laravel;

use Carbon\Carbon;
use Illuminate\Support\Collection;

trait Revisionable
{
    /**
     * Boot the trait for a model.
     */
    protected static function bootRevisionable()
    {
        static::observe(Listener::class);
    }

    /**
     * Get record version at given timestamp.
     *
     * @param \DateTime|string $timestamp DateTime|Carbon object or parsable date string @see strtotime()
     *
     * @return \Sofa\Revisionable\Laravel\Revision|\Sofa\Revisionable\laravel\Presenter|null
     */
    public function snapshot($timestamp)
    {
        $revision = $this->revisions()
                         ->where('created_at', '<=', Carbon::parse($timestamp))
                         ->first();

        return $this->wrapRevision($revision);
    }

    /**
     * Get record version at given step back in history.
     *
     * @param int $step
     *
     * @return \Sofa\Revisionable\Laravel\Revision|\Sofa\Revisionable\laravel\Presenter|null
     */
    public function historyStep($step)
    {
        return $this->wrapRevision($this->revisions()->skip($step)->first());
    }

    /**
     * Determine if model has history at given timestamp if provided or any at all.
     *
     * @param \DateTime|string $timestamp DateTime|Carbon object or parsable date string @see strtotime()
     *
     * @return bool
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
        return array_map('unserialize',
            array_diff(array_map('serialize', $this->getNewAttributes()), array_map('serialize', $this->getOldAttributes())));
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
     * @param array $attributes
     *
     * @return array
     */
    protected function prepareAttributes(array $attributes)
    {
        return array_map(function ($attribute) {
            if ($attribute instanceof DateTime) {
                return $this->fromDateTime($attribute);
            } elseif ($this->isJSON($attribute)) {
                return $this->fromJson($attribute);
            } else {
                return (string) $attribute;
            }
        }, $attributes);
    }

    /**
     * Check if a string is JSON.
     *
     * @param $string
     * @return bool
     */
    protected function isJSON($string){
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }

    /**
     * Get an array of revisionable attributes.
     *
     * @param array $values
     *
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
     * Model has many Revisions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function revisions()
    {
        return $this->morphMany(Revision::class, 'revisionable', 'revisionable_type', 'row_id')
                    ->ordered();
    }

    /**
     * Model has one latestRevision.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function latestRevision()
    {
        return $this->morphOne(Revision::class, 'revisionable', 'revisionable_type', 'row_id')
                    ->ordered();
    }

    /**
     * Accessor for revisions property.
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
     * Accessor for latestRevision attribute.
     *
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
     * @param \Sofa\Revisionable\Laravel\Revision|\Illuminate\Database\Eloquent\Collection $history
     *
     * @return \Sofa\Revisionable\Laravel\Presenter|\Sofa\Revisionable\Laravel\Revision
     */
    public function wrapRevision($history)
    {
        if ($history && $presenter = $this->getRevisionPresenter()) {
            return $presenter::make($history, $this);
        }

        return $history;
    }

    /**
     * Get revision presenter class for the model.
     *
     * @return string|null
     */
    public function getRevisionPresenter()
    {
        if (!property_exists($this, 'revisionPresenter')) {
            return;
        }

        return class_exists($this->revisionPresenter)
                ? $this->revisionPresenter
                : Presenter::class;
    }

    /**
     * Get all updates for a given field.
     *
     * @param  string $field
     * @return Illuminate\Support\Collection
     */
    public function getFieldHistory(string $field) : Collection
    {
        return $this->revisions->map(function ($revision) use ($field) : ?array {
            if ($revision->old($field) == $revision->new($field)) {
                return null;
            }

            return [
                'created_at' => (string) $revision->created_at,
                'user_id' => $revision->executor->id ?? null,
                'user_email' => $revision->executor->email ?? null,
                'old' => $revision->old($field),
                'new' => $revision->new($field),
            ];
        })->filter()->values();
    }
}
