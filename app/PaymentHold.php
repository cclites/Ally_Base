<?php
namespace App;


/**
 * \App\PaymentHold
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $business_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $notes
 * @property string|null $check_back_on
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentHold newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentHold newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentHold query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentHold whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentHold whereCheckBackOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentHold whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentHold whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentHold whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentHold whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentHold whereUserId($value)
 * @mixin \Eloquent
 */
class PaymentHold extends AuditableModel
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'payment_holds';
    protected $fillable = ['notes', 'check_back_on'];
}
