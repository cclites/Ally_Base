<?php

namespace App;

use App\Traits\IsUserRole;
use Illuminate\Database\Eloquent\Model;

class OfficeUser extends Model
{
    use IsUserRole;

    protected $table = 'office_users';
    public $timestamps = false;
    public $fillable = [];

    public function businesses()
    {
        return $this->belongsToMany(Business::class, 'business_office_users');
    }

}
