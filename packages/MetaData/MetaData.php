<?php
namespace Packages\MetaData;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MetaData
 * @package Packages\MetaData
 *
 * @property int $id
 * @property string $metable_type
 * @property string $metable_id
 * @property string $key
 * @property string $value
 */
class MetaData extends Model
{
    protected $table = 'meta_data';
    protected $fillable = ['key', 'value'];

    public function metable()
    {
        return $this->morphTo();
    }
}
