<?php
namespace App;

/**
 * App\TransactionRefund
 *
 * @property int $id
 * @property int $issued_transaction_id
 * @property int $issued_payment_id
 * @property int $refunded_transaction_id
 * @property int $refunded_payment_id
 * @property float $amount
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Payment $issuedPayment
 * @property-read \App\GatewayTransaction $issuedTransaction
 * @property-read \App\Payment $refundedPayment
 * @property-read \App\GatewayTransaction $refundedTransaction
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TransactionRefund whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TransactionRefund whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TransactionRefund whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TransactionRefund whereIssuedPaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TransactionRefund whereIssuedTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TransactionRefund whereRefundedPaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TransactionRefund whereRefundedTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TransactionRefund whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TransactionRefund extends AuditableModel
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
