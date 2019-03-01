<?php
namespace App\Traits;

use App\Billing\GatewayTransaction;
use App\Shift;

trait ShiftReportFilters
{
    public function forShift(Shift $shift) {
        $this->query()->where('id', $shift->id);
        return $this;
    }

    public function forTransaction(GatewayTransaction $transaction) {
        if ($transaction->payment) {
            $this->query()->whereHas('payment', function($q) use ($transaction) {
                $q->where('payments.id', $transaction->payment->id);
            });
        }
        elseif ($transaction->deposit) {
            $this->query()->whereHas('deposits', function($q) use ($transaction) {
                $q->where('deposits.id', $transaction->deposit->id);
            });
        }
        return $this;
    }

    public function forPaymentMethod($method_type)
    {
        $this->query()->whereHas('client', function($q) use ($method_type) {
            $q->where('default_payment_type', $method_type);
        });
        return $this;
    }

    public function forReconciliationReport(GatewayTransaction $transaction) {
        if ($transaction->deposit) {
            $this->query()->where('provider_fee', '>', 0);
        }
        return $this;
    }

}