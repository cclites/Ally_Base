<?php
namespace App;

/**
 * App\ClientAgreementStatusHistory
 *
 * @property int $id
 * @property int $client_id
 * @property string $status
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientAgreementStatusHistory whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientAgreementStatusHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientAgreementStatusHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientAgreementStatusHistory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientAgreementStatusHistory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ClientAgreementStatusHistory extends BaseModel
{
    protected $table = 'client_agreement_status_history';
    protected $guarded = ['id'];
}
