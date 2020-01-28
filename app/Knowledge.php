<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * App\Knowledge
 *
 * @property int $id
 * @property string $type
 * @property string $title
 * @property string $slug
 * @property string|null $body
 * @property string|null $youtube_id
 * @property string|null $video_attachment_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Attachment[] $attachments
 * @property-read array $assigned_roles
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\KnowledgeRole[] $roles
 * @property-read \App\Attachment $video
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Knowledge forRoles($roles)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Knowledge whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Knowledge whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Knowledge whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Knowledge whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Knowledge whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Knowledge whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Knowledge whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Knowledge whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Knowledge whereVideoAttachmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Knowledge whereYoutubeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Knowledge withKeyword($keyword)
 * @mixin \Eloquent
 */
class Knowledge extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'knowledge';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    public $guarded = ['id'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    public $with = ['attachments', 'video', 'roles'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    public $appends = ['assigned_roles'];

    /**
     * @var string  The column to sort by default when using ordered()
     */
    protected $orderedColumn = 'title';

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        // create the slug automatically
        static::saving(function (Knowledge $item) {
            if (is_null($item->slug)) {
                $item->slug = self::uniqueSlug($item->title, $item->id);
            }
        });
    }

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************
    
    /**
     * Get the Knowledge Attachments relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function attachments()
    {
        return $this->belongsToMany(Attachment::class, 'knowledge_attachments');
    }

    /**
     * Gets the related video file information.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
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
        return $this->hasMany(KnowledgeRole::class);
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************
    
    /**
     * Get the assigned roles as strings.
     *
     * @return array
     */
    public function getAssignedRolesAttribute()
    {
        return $this->roles->pluck('role')->toArray();
    }
    
    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    /**
     * Scope to add keyword search to title and body.
     *
     * @param \Illuminate\Database\Query\Builder query
     * @param string $keyword
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWithKeyword($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where('title', 'like', "%$keyword%")
                ->orWhere('body', 'like', "%$keyword%");
        });
    }

    /**
     * Add the scope for a specific role.
     *
     * @param \Illuminate\Database\Query\Builder query
     * @param array|string $roles
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForRoles($query, $roles)
    {
        if (! is_array($roles)) {
            $roles = [$roles];
        }

        return $query->whereHas('roles', function ($q) use ($roles) {
            return $q->whereIn('role', $roles);
        });
    }

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    /**
     * Generates a unique slug from the given string.
     *
     * @param string $title
     * @param int $ignoreId
     * @return string
     */
    public static function uniqueSlug($title, $ignoreId)
    {
        $slug = Str::slug($title);
        $index = 1;

        while (true) {
            if (! Knowledge::where('slug', $slug)->where('id', '!=', $ignoreId)->exists()) {
                break;
            }

            $index++;
            $slug = Str::slug($title) . $index;
        }

        return $slug;
    }

    /**
     * Update the roles from a string array.
     *
     * @param array $roles
     * @return bool
     */
    public function syncRoles($roles)
    {
        $this->roles()->delete();

        foreach ($roles as $role) {
            $this->roles()->create(['role' => $role]);
        }

        return true;
    }
}
