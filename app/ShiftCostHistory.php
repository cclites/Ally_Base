<?php
namespace App;

/**
 * App\ShiftCostHistory
 *
 * @property int $id
 * @property float $caregiver_shift
 * @property float $caregiver_expenses
 * @property float $caregiver_mileage
 * @property float $caregiver_total
 * @property float $provider_fee
 * @property float $ally_fee
 * @property float $total_cost
 * @property float $ally_pct
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Shift $shift
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftCostHistory whereAllyFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftCostHistory whereAllyPct($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftCostHistory whereCaregiverExpenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftCostHistory whereCaregiverMileage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftCostHistory whereCaregiverShift($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftCostHistory whereCaregiverTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftCostHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftCostHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftCostHistory whereProviderFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftCostHistory whereTotalCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftCostHistory whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftCostHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftCostHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftCostHistory query()
 */
class ShiftCostHistory extends AuditableModel
{
    protected $table = 'shift_cost_history';
    protected $guarded = [];

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'id');
    }
}