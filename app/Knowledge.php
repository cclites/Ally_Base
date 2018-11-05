<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Knowledge extends Model
{
    protected $table = 'knowledge';

    public $guarded = ['id'];

    public $with = ['attachments', 'video'];

    public static function boot() {

        // create the slug automatically 
        static::saving(function(Knowledge $item) {
            if (is_null($item->slug)) {
                $item->slug = self::uniqueSlug($item->title, $item->id);
            }
        });
    }

    /**
     * Get the Knowledge Attachments relation.
     *
     * @return HasMany
     */
    public function attachments()
    {
        return $this->belongsToMany(Attachment::class, 'knowledge_attachments');
    }
    
    /**
     * Scope to add keyword search to title and body.
     *
     * @param [type] $query
     * @param string $keyword
     * @return void
     */
    public function scopeWithKeyword($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where('title', 'like', "%$keyword%")
                ->orWhere('body', 'like', "%$keyword%");
        });
    }

    /**
     * Generates a unique slug from the given string.
     *
     * @param string $title
     * @return string
     */
    public static function uniqueSlug($title, $ignoreId)
    {
        $slug = str_slug($title);
        $index = 1;

        while (true) {
            if (! Knowledge::where('slug', $slug)->where('id', '!=', $ignoreId)->exists()) {
                break;
            }

            $index++;
            $slug = str_slug($title) . $index;
        }

        return $slug;
    }

    /**
     * Gets the related video file information.
     *
     * @return void
     */
    public function video()
    {
        return $this->hasOne(Attachment::class, 'id', 'video_attachment_id');
    }
}
