<?php namespace Sofa\Revisionable\Laravel4;

use Illuminate\Database\Eloquent\Model;
use \Config;

class Revision extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table;
    
    public function __construct()
    {
        $this->table = Config::get('revisionable::table') :? 'revisions';
    }

    /**
     * {@inheritdoc}
     */
    public static function boot()
    {
        parent::boot();

        // Make it read-only
        static::saving(function () {
            return false;
        });
    }
}
