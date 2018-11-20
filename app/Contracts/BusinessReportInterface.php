<?php
namespace App\Contracts;

use App\User;

interface BusinessReportInterface
{
    public function forBusinesses(array $businessIds = null);
    public function forRequestedBusinesses(array $businessIds = null, User $authorizedUser = null);
}