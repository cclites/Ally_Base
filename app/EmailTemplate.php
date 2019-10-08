<?php


namespace App;

use App\Contracts\BelongsToBusinessesInterface;
use App\Traits\BelongsToOneBusiness;
use Illuminate\Database\Eloquent\Builder;

class EmailTemplate extends BaseModel implements BelongsToBusinessesInterface
{
    use BelongsToOneBusiness;

    protected $fillable = ['business_id', 'greeting', 'body', 'type', 'id'];

    const TEMPLATE_CG_EXPIRATION = 'caregiver_expiration';
    const TEMPLATE = [
        self::TEMPLATE_CG_EXPIRATION,
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function renderTemplate(){

    }

}