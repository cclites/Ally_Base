<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToOneBusiness;
use App\Business;

class BusinessCommunications extends BaseModel{

    use BelongsToOneBusiness;

    protected $table = "business_communications_settings";

    public $timestamps = false;

    public function business(){
        return $this->belongsTo(Business::class);
    }

}
