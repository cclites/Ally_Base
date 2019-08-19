<?php
namespace App;

use App\CustomField;

/**
 * App\ClientMeta
 *
 * @property int $id
 * @property int $client_id
 * @property string $key
 * @property string|null $value
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientMeta whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientMeta whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientMeta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientMeta whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientMeta whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientMeta whereValue($value)
 * @mixin \Eloquent
 */
class ClientMeta extends BaseModel
{
    /**
    * The database table associated with this model
    * 
    * @var string
    */
    protected $table = 'client_meta';

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

            $options = $field->options->where( 'value', $this->value )->first();
            if( is_object( $options ) ) return $options->label;
            return '';
        }

        return $this->value;
    }
}
