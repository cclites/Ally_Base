<?php

namespace App;

/**
 * App\QuickbooksCustomer
 *
 * @property int $id
 * @property int $business_id
 * @property string $customer_id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read \App\Business $business
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Client[] $client
 * @property-read int|null $client_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\QuickbooksCustomer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\QuickbooksCustomer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\QuickbooksCustomer query()
 * @mixin \Eloquent
 */
class QuickbooksCustomer extends AuditableModel
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    public $with = [];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    /**
     * @var string  The column to sort by default when using ordered()
     */
    protected $orderedColumn = 'name';

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the Business relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the Client relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function client()
    {
        return $this->hasMany(Client::class);
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

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
            'name' => $faker->name,
        ];
    }
}
