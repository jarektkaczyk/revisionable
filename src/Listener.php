<?php namespace Sofa\Revisionable;

interface Listener
{
    /**
     * @return false|null
     */
    public function onCreated($row);

    /**
     * @return false|null
     */
    public function onUpdated($row);

    /**
     * @return false|null
     */
    public function onDeleted($row);

    /**
     * @return false|null
     */
    public function onRestored($row);
}
