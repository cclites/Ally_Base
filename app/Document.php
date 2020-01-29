<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use File;

/**
 * App\Document
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $name
 * @property string $filename
 * @property string $original_filename
 * @property string|null $type
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $description
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\User|null $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Document onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Document whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Document whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Document whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Document whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Document whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Document whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Document whereOriginalFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Document whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Document whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Document whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Document withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Document withoutTrashed()
 * @mixin \Eloquent
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Document newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Document newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Document query()
 */
class Document extends AuditableModel
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

    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************
    use \App\Traits\ScrubsForSeeding;

    /**
     * Get an array of scrubbed data to replace the original.
     *
     * @param \Faker\Generator $faker
     * @param bool $fast
     * @param null|\Illuminate\Database\Eloquent\Model $item
     * @return array
     */
    public static function getScrubbedData(\Faker\Generator $faker, bool $fast, ?\Illuminate\Database\Eloquent\Model $item) : array
    {
        return [
            'original_filename' => $faker->word.'.pdf',
            'description' => $faker->word,
        ];
    }
}
