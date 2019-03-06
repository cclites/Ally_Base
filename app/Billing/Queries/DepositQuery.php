<?php
namespace App\Billing\Queries;

use App\Billing\Deposit;
use App\Business;
use App\Caregiver;
use Illuminate\Database\Eloquent\Model;

class DepositQuery extends BaseQuery
{
    use BelongsToBusinessesQueries;

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

    function hasAmountAvailable(): self
    {
        $this->where('success', true)
            ->where('created_at', '>=', '2019-01-01 00:00:00') // Prevent pre-migration missing applications from showing as available payments
            ->whereRaw('(SELECT COALESCE(SUM(amount_applied), 0) FROM invoice_deposits WHERE deposit = deposits.id) < deposit.amount');

        return $this;
    }
}