<?php namespace Sofa\Revisionable;

interface Revisionable
{

    /**
     * Boot Revisionable Logger.
     *
     * @return void
     */
    protected static function bootLogger();

    /**
     * Register listener for given event.
     *
     * @param  mixed
     * @return void
     */
    protected static function registerListeners();

    /**
     * Get an array of updated revisionable attributes.
     *
     * @return array
     */
    protected function getDiff();

    /**
     * Get an array of original revisionable attributes.
     *
     * @return array
     */
    protected function getOldAttributes();

    /**
     * Get an array of current revisionable attributes.
     *
     * @return array
     */
    protected function getNewAttributes();

    /**
     * Get an array of revisionable attributes.
     *
     * @param  array  $values
     * @return array
     */
    protected function getRevisionableItems(array $values)

    /**
     * Attributes being revisioned.
     *
     * @var array
     */
    protected function getRevisionable();

    /**
     * Attributes hidden from revisioning if revisionable are not provided.
     *
     * @var array
     */
    protected function getNonRevisionable();

    /**
     * Determine if model should be revisioned.
     *
     * @return boolean
     */
    protected function isRevisioned();

    /**
     * Disable revisioning for current instance.
     *
     * @return void
     */
    protected function disableRevisioning();

    /**
     * Enable revisioning for current instance.
     *
     * @return void
     */
    protected function enableRevisioning();
}
