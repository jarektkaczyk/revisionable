<?php namespace Sofa\Revisionable\Laravel4;

use Illuminate\Database\Eloquent\Model;

class Revision extends Model {

	public static function boot()
	{
		parent::boot();

		// Make it read-only
		static::saving(function () { return false; });
	}

}
