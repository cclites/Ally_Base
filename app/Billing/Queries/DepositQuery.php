<?php
namespace App\Billing\Queries;

use App\Billing\Deposit;
use App\Business;
use App\Caregiver;
use Illuminate\Database\Eloquent\Model;

class DepositQuery extends BaseQuery
{

    /**
     * Return an empty instance of the Model this class queries
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    function getModelInstance(): Model
    {
        return new Deposit();
    }

    function forCaregiver(Caregiver $caregiver)
    {
        $this->where('caregiver_id', $caregiver->id);

        return $this;
    }

    function forBusiness(Business $business)
    {
        $this->where('business_id', $business->id);

        return $this;
    }
}