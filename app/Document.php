<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\File;

/**
 * Class Document
 * @package App
 * @property string $name
 * @property string $filename
 * @property string $original_filename
 * @property string $type
 * @property string $description
 */
class Document extends Model
{
    use SoftDeletes;

    protected $guarded = ['id', 'user_id'];

    protected static function boot()
    {
        parent::boot();

        // remove the document from the file system after it is deleted
        static::deleted(function ($document) {
            File::delete($document->path());
        });

    }

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    /**
     * User who uploaded the document.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    ///////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

    public function path()
    {
        return storage_path('app/documents/'.$this->filename);
    }
}
