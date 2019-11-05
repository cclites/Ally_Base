<?php

namespace App;

use App\Contracts\BelongsToBusinessesInterface;
use App\Traits\BelongsToOneBusiness;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToOneChain;

class TenNinetyNine extends AuditableModel implements BelongsToBusinessesInterface
{
    use BelongsToOneBusiness;

    protected $table = '1099s';

    // RELATIONS //
    public function client(){
        return hasOne(Client::class);
    }

    public function caregiver(){
        return hasOne(Caregiver::class);
    }

    public function getBusinessIds()
    {
        // TODO: Implement getBusinessIds() method.
    }

    public function belongsToBusiness($business)
    {
        // TODO: Implement belongsToBusiness() method.
    }

    public function belongsToAnyBusiness(array $businesses)
    {
        // TODO: Implement belongsToAnyBusiness() method.
    }

    public function filterAttachedBusinesses(array $businessIds)
    {
        // TODO: Implement filterAttachedBusinesses() method.
    }

    public function sharesBusinessWith(BelongsToBusinessesInterface $entity)
    {
        // TODO: Implement sharesBusinessWith() method.
    }

    public function scopeForBusinesses(Builder $builder, array $businessIds)
    {
        // TODO: Implement scopeForBusinesses() method.
    }

    public function scopeForRequestedBusinesses(Builder $builder, array $businessIds = null, User $authorizedUser = null)
    {
        // TODO: Implement scopeForRequestedBusinesses() method.
    }
}
