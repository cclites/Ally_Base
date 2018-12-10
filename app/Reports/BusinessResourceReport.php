<?php
namespace App\Reports;

use App\Contracts\BusinessReportInterface;
use App\User;

abstract class BusinessResourceReport extends BaseReport implements BusinessReportInterface
{
    public function forBusinesses(array $businessIds = null)
    {
        $this->query()->forBusinesses($businessIds);
        return $this;
    }

    public function forRequestedBusinesses(array $businessIds = null, User $authorizedUser = null)
    {
        $this->query()->forRequestedBusinesses($businessIds, $authorizedUser);
        return $this;
    }

}