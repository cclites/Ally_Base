<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Knowledge extends Model
{
    protected $table = 'knowledge';

    public $guarded = ['id'];

    public $with = ['attachments', 'video', 'roles'];

    public $appends = ['assigned_roles'];

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

    /**
     * Get the assigned roles relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function roles()
    {
//        return $this->belongsToMany(KnowledgeRole::class, 'knowledge_roles', 'knowledge_id', 'role');
        return $this->hasMany(KnowledgeRole::class);
    }

    /**
     * Get the assigned roles as strings.
     *
     * @return array
     */
    public function getAssignedRolesAttribute()
    {
        return $this->roles->pluck('role')->toArray();    
    }

    /**
     * Add the scope for a specific role.
     *
     * @param \Illuminate\Database\Query\Builder query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForRole($query, $role)
    {
        return $query->whereHas('roles', function ($q) use ($role) {
            return $q->where('role', $role);
        });
    }

    public function syncRoles($roles)
    {
        $this->roles()->delete();

        foreach ($roles as $role) {
            $this->roles()->create(['role' => $role]);
        }

        return true;
    }
}
