<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Attachment
 *
 * @property int $id
 * @property string $filename
 * @property string $name
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read string $url
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Attachment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Attachment whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Attachment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Attachment whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Attachment whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Attachment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Attachment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Attachment query()
 */
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
