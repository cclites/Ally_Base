<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ClientMeta
 *
 * @property int $id
 * @property int $client_id
 * @property string $key
 * @property string|null $value
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientMeta whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientMeta whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientMeta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientMeta whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientMeta whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientMeta whereValue($value)
 * @mixin \Eloquent
 */
class ClientMeta extends Model
{
    protected $table = 'client_meta';
    protected $fillable = ['key', 'value'];
}
