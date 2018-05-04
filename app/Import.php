<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Import extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'imports';
    protected $fillable = [
        'name',
        'user_id',
    ];

    public function shifts()
    {
        return $this->hasMany(Shift::class, 'import_id', 'id');
    }
}
