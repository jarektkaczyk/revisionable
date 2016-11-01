<?php

namespace Sofa\Revisionable;

interface UserProvider
{
    /**
     * @return string|null
     */
    public function getUser();

    /**
     * @return integer
     */
    public function getUserId();
}
