<?php
namespace App;

use App\CustomField;
use App\Caregiver;

/**
 * App\CaregiverMeta
 *
 * @property int $id
 * @property int $caregiver_id
 * @property string $key
 * @property string|null $value
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverMeta whereCaregiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverMeta whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverMeta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverMeta whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverMeta whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverMeta whereValue($value)
 * @mixin \Eloquent
 */
class CaregiverMeta extends BaseModel
{    
    /**
    * The database table associated with this model
    * 
    * @var string
    */
    protected $table = 'caregiver_meta';

    /**
    * The attributes that are mass assignable
    * 
    * @var array
    */
    protected $fillable = ['key', 'value'];

    /**
     * Get a displayable value for this custom field
     *
     * @return string
     */
    public function display()
    {

        $field = CustomField::forAuthorizedChain()
            ->where('key', $this->key)
            ->with('options')
            ->first();

        if( $field->type == 'dropdown' ) {
            // this is the old search.. I was unable to reproduce the server error, however the error suggested grbbign a propery of a non-object so I guarded against that.
            // return $field->options->where( 'value', $this->value )->first()->label;
            // https://sentry.io/organizations/jtr-solutions/issues/1123330667/?project=1391475&query=is%3Aunresolved

            $options = $field->options->where( 'value', $this->value )->first();
            if( is_object( $options ) ) return $options->label;
            return '';
        }
        
        return $this->value;
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
            'value' => $faker->word,
        ];
    }
}
