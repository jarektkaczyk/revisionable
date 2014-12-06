<?php namespace Sofa\Revisionable;

interface Listener {

	public function onCreated($row);
	public function onUpdated($row);
	public function onDeleted($row);
	public function onRestored($row);

}
