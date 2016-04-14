<?php namespace Sofa\Revisionable;

interface UserProvider
{

    /**
     * @return string|null
     */
    public function getUser();

    /**
     * @return object|array|null
     */
    public function getUserModel($id);
}
