<?php namespace Sofa\Revisionable\Tests\Laravel4;

use Sofa\Revisionable\Laravel4\RevisionableTrait;
use Sofa\Revisionable\Revisionable;
use Illuminate\Database\Eloquent\Model;

class EloquentRevisionableStub extends Model implements Revisionable
{
    protected $revisionableConnection = 'custom_connection';
    
    use RevisionableTrait {
    	getRevisionableLogger as getLogger;
    }

    public function getRevisionableLogger()
    {
    	return static::getLogger();
    }
}
