<?php
namespace App;


/**
 * App\PaymentHold
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $business_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentHold whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentHold whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentHold whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentHold whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentHold whereUserId($value)
 * @mixin \Eloquent
 */
class PaymentHold extends AuditableModel
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'payment_holds';
}
