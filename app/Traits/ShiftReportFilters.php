<?php
namespace App\Traits;

use App\Billing\GatewayTransaction;
use App\Billing\Payer;
use App\Business;
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

    public function forPaymentMethod($methodClass)
    {
        $method_type = maps_from_class($methodClass) ?? $methodClass;
        $this->query()->whereHas('client', function($q) use ($methodClass, $method_type) {
            $q->where('default_payment_type', $method_type);
            if ($methodClass === Business::class) {
                $q->orWhereHas('primaryPayer', function($q) {
                    $q->where('payer_id', '!=', Payer::PRIVATE_PAY_ID);
                });
            }
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