<?php namespace Sofa\Revisionable;

interface Listener
{
    /**
     * @return null
     */
    public function onCreated($row);

    /**
     * @return null
     */
    public function onUpdated($row);

    /**
     * @return null
     */
    public function onDeleted($row);

    /**
     * @return null
     */
    public function onRestored($row);
}
