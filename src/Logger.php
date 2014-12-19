<?php namespace Sofa\Revisionable;

interface Logger
{
	/**
	 * Log data revisions.
	 *
	 * @param  string  $type
	 * @param  string  $table
	 * @param  integer $id
	 * @param  array   $old
	 * @param  array   $new
	 * @param  mixed   $user
	 *
	 * @return  void
	 */
    public function revisionLog($type, $table, $id, array $old, array $new, $user);
}
