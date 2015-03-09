<?php namespace Sofa\Revisionable;

interface Revisionable
{
    /**
     * Boot Revisionable Logger.
     *
     * @return void
     */
    public static function bootLogger();

    /**
     * Register listener for given event.
     *
     * @param  mixed
     * @return void
     */
    public static function registerListeners();

    /**
     * Get an array of updated revisionable attributes.
     *
     * @return array
     */
    public function getDiff();

    /**
     * Get an array of original revisionable attributes.
     *
     * @return array
     */
    public function getOldAttributes();

    /**
     * Get an array of current revisionable attributes.
     *
     * @return array
     */
    public function getNewAttributes();

    /**
     * Get an array of revisionable attributes.
     *
     * @param  array  $values
     * @return array
     */
    public function getRevisionableItems(array $values);

    /**
     * Attributes being revisioned.
     *
     * @var array
     */
    public function getRevisionable();

    /**
     * Attributes hidden from revisioning if revisionable are not provided.
     *
     * @var array
     */
    public function getNonRevisionable();

    /**
     * Determine if model should be revisioned.
     *
     * @return boolean
     */
    public function isRevisioned();

    /**
     * Disable revisioning for current instance.
     *
     * @return void
     */
    public function disableRevisioning();

    /**
     * Enable revisioning for current instance.
     *
     * @return void
     */
    public function enableRevisioning();

    /**
     * Determine if model has any revisions history.
     *
     * @return boolean
     */
    public function hasRevisions();
}
