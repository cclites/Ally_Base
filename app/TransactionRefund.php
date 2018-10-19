<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransactionRefund extends Model
{
    protected $table = 'transaction_refunds';
    protected $guarded = ['id'];

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    public function issuedTransaction() {
        return $this->belongsTo(GatewayTransaction::class, 'issued_transaction_id');
    }

    public function refundedTransaction() {
        return $this->belongsTo(GatewayTransaction::class, 'refunded_transaction_id');
    }

    public function issuedPayment() {
        return $this->belongsTo(Payment::class, 'issued_payment_id');
    }

    public function refundedPayment() {
        return $this->belongsTo(Payment::class, 'refunded_payment_id');
    }
}
