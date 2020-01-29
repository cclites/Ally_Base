<?php
namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Illuminate\Support\Str;

/**
 * App\ClientReferralServiceAgreement
 *
 * @property int $id
 * @property int $client_id
 * @property float $referral_fee
 * @property float $per_visit_referral_fee
 * @property float $per_visit_assessment_fee
 * @property string $termination_notice
 * @property string $executed_by
 * @property array $payment_options
 * @property string|null $agreement_file
 * @property mixed $signature_one
 * @property mixed $signature_two
 * @property mixed $signature_client
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $signature_one_text
 * @property string|null $signature_two_text
 * @property string|null $signature_client_text
 * @property string|null $executed_by_ip
 * @property-read \App\Client $client
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientReferralServiceAgreement whereAgreementFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientReferralServiceAgreement whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientReferralServiceAgreement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientReferralServiceAgreement whereExecutedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientReferralServiceAgreement whereExecutedByIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientReferralServiceAgreement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientReferralServiceAgreement wherePaymentOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientReferralServiceAgreement wherePerVisitAssessmentFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientReferralServiceAgreement wherePerVisitReferralFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientReferralServiceAgreement whereReferralFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientReferralServiceAgreement whereSignatureClient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientReferralServiceAgreement whereSignatureClientText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientReferralServiceAgreement whereSignatureOne($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientReferralServiceAgreement whereSignatureOneText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientReferralServiceAgreement whereSignatureTwo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientReferralServiceAgreement whereSignatureTwoText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientReferralServiceAgreement whereTerminationNotice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientReferralServiceAgreement whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientReferralServiceAgreement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientReferralServiceAgreement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientReferralServiceAgreement query()
 */
class ClientReferralServiceAgreement extends BaseModel
{
    protected $guarded = ['id'];

    protected $casts = ['payment_options' => 'array'];

    protected $dates = ['created_at', 'updated_at'];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($rsa) {
            $rsa->createPdf();
        });
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function createPdf()
    {
        $this->load('client.business');
        $pdf = PDF::loadView('business.clients.service_referral_agreement_doc', ['rsa' => $this, 'override_ally_logo' => $this->client->business->logo]);
        $dir = storage_path('app/documents/');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 493, true);
        }
        $filename = Str::slug($this->client->id . ' ' . $this->client->name.' Referral Service Agreement').'.pdf';
        $filePath = $dir . '/' . $filename;
        if (config('app.env') == 'local') {
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
        }
        $response = $pdf->save($filePath);

        if ($response) {
            DB::transaction(function() use ($response, $filePath) {
                $this->client->documents()->create([
                    'filename' => File::basename($filePath),
                    'original_filename' => File::basename($filePath),
                    'description' => 'Client Referral Service Agreement'
                ]);
            });
        }
    }

}
