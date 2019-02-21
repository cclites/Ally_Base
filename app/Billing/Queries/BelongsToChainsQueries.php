<?php
namespace App\Billing\Queries;

use App\User;

trait BelongsToChainsQueries
{
    /**
     * @param int|array $chains
     * @return $this
     */
    function forChains($chains): self
    {
        parent::forChains($chains);

        return $this;
    }

    function forAuthorizedChain(User $authorizedUser = null): self
    {
        parent::forAuthorizedChain($authorizedUser);

        return $this;
    }
}