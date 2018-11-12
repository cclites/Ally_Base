<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attachments';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    public $guarded = ['id'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    public $appends = ['url'];

    /**
     * Provides the downloadable URL for the attachment.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return route('knowledge.attachment', ['attachment' => $this->filename]);
    }
}
