<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\SetupStatusHistory
 *
 * @property int $id
 * @property int $user_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SetupStatusHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SetupStatusHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SetupStatusHistory query()
 * @mixin \Eloquent
 */
class SetupStatusHistory extends Model
{
    protected $table = 'user_setup_status_history';
    protected $guarded = ['id'];
}
