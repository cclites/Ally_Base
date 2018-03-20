<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Import extends Model
{
    protected $table = 'imports';
    protected $fillable = [
        'user_id',
    ];

    public function shifts()
    {
        return $this->hasMany(Shift::class, 'import_id', 'id');
    }
}
