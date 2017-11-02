<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
    protected $guarded = ['id', 'user_id'];

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

}
