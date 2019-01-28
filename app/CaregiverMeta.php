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

        if($field->type == 'dropdown') {
            return $field->options->where('value', $this->value)->first()->label;
        }
        
        return $this->value;
    }
}
