<?php namespace Sofa\Revisionable\Laravel5;

use Illuminate\Database\Eloquent\Model;

class Revision extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected static $customTable;
    
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

    /**
     * Accessor for old property
     *
     * @return array
     */
    public function getOldAttribute($old)
    {
        return (array) json_decode($old);
    }

    /**
     * Accessor for new property
     *
     * @return array
     */
    public function getNewAttribute($new)
    {
        return (array) json_decode($new);
    }
 
    /**
     * {@inheritdoc}
     */
    public function getTable()
    {
        $table = $this->table ?: static::$customTable;

        return ($table) ?: parent::getTable();
    }

    /**
     * Set custom table name for the model.
     *
     * @param  string  $table
     * @return void
     */
    public static function setCustomTable($table)
    {
        if ( ! isset(static::$customTable))
        {
            static::$customTable = $table;
        }
    }
}
