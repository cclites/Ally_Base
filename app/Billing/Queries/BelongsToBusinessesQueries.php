<?php


namespace App\Billing\Queries;


use App\User;

trait BelongsToBusinessesQueries
{
    function forBusinesses(array $businessIds): self
    {
        parent::forBusinesses($businessIds);

        return $this;
    }

    function forRequestedBusinesses(array $businessIds = null, User $authorizedUser = null): self
    {
        parent::forRequestedBusinesses($businessIds, $authorizedUser);

        return $this;
    }
}