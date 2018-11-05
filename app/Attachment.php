<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $table = 'attachments';

    public $guarded = ['id'];
    
    public $appends = ['url'];

    /**
     * Provides the downloadable URL for the attachment.
     *
     * @return void
     */
    public function getUrlAttribute()
    {
        return route('knowledge.attachment', ['attachment' => $this->filename]);
    }
}
