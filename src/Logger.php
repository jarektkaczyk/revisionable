<?php namespace Sofa\Revisionable;

interface Logger
{
    public function revisionLog($type, $table, $id, array $old, array $new, $user);
}
