<?php namespace Sofa\Revisionable;

interface Listener
{
    /**
     * @return null
     */
    public function onCreated(Revisionable $row);

    /**
     * @return null
     */
    public function onUpdated(Revisionable $row);

    /**
     * @return null
     */
    public function onDeleted(Revisionable $row);

    /**
     * @return null
     */
    public function onRestored(Revisionable $row);
}
