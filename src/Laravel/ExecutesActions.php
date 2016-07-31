<?php

namespace Sofa\Revisionable\Laravel;

trait ExecutesActions
{
    /**
     * Actions relation based on the Revisionable models.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function actions()
    {
        return $this->hasMany(Revision::class, 'user_id')->ordered();
    }

    /**
     * Latest action executed on Revisionable models.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestAction()
    {
        return $this->hasOne(Revision::class, 'user_id')->ordered();
    }

    /**
     * Accessor for actions property.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActionsAttribute()
    {
        if (!$this->relationLoaded('actions')) {
            $this->load('actions');
        }

        return $this->getRelation('actions')->load('revisioned')->map(function ($revision) {
            if ($revisioned = $revision->revisioned) {
                return $revisioned->wrapRevision($revision);
            }

            return $revision;
        });
    }

    /**
     * Accessor for latestAction attribute.
     *
     * @link https://laravel.com/docs/eloquent-mutators#accessors-and-mutators
     *
     * @return \Sofa\Revisionable\Laravel\Presenter|\Sofa\Revisionable\Laravel\Revision
     */
    public function getLatestActionAttribute()
    {
        if (!$this->relationLoaded('latestAction')) {
            $this->load('latestAction');
        }

        return $this->wrapRevision($this->getRelation('latestAction'));
    }
}
