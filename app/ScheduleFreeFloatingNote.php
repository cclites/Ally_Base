<?php

namespace App;

use App\Contracts\BelongsToBusinessesInterface;
use App\Traits\BelongsToOneBusiness;

class ScheduleFreeFloatingNote extends BaseModel implements BelongsToBusinessesInterface
{
    use BelongsToOneBusiness;

    protected $guarded = [ 'id' ];
    protected $dates = [];
    protected $with = [];
    protected $appends = [];
    protected $orderedColumn = 'created_at';
    protected $casts = [];

}
