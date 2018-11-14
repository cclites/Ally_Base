<?php
namespace App;

/**
 * App\BusinessChain
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Business[] $businesses
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Caregiver[] $caregivers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\OfficeUser[] $users
 * @mixin \Eloquent
 */
class BusinessChain extends AuditableModel
{

    protected $table = 'business_chains';
    protected $guarded = ['id'];

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    public function businesses()
    {
        return $this->hasMany(Business::class, 'chain_id');
    }

    public function caregivers()
    {
        return $this->belongsToMany(Caregiver::class, 'chain_caregivers', 'chain_id')
            ->withTimestamps();
    }

    public function users()
    {
        return $this->hasMany(OfficeUser::class, 'chain_id');
    }
}