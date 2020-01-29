<?php

namespace App;

use App\Contracts\BelongsToBusinessesInterface;
use App\Services\TaxDocumentPrinter;
use App\Traits\BelongsToOneBusiness;
use mikehaertl\pdftk\Pdf;

/**
 * App\Caregiver1099
 *
 * @property int $id
 * @property int $year
 * @property int|null $caregiver_id
 * @property int|null $client_id
 * @property int|null $business_id
 * @property string|null $client_first_name
 * @property string|null $client_last_name
 * @property mixed|null $client_ssn
 * @property string|null $client_address1
 * @property string|null $client_address2
 * @property string|null $client_city
 * @property string|null $client_state
 * @property string|null $client_zip
 * @property string|null $caregiver_first_name
 * @property string|null $caregiver_last_name
 * @property mixed|null $caregiver_ssn
 * @property string|null $caregiver_address1
 * @property string|null $caregiver_address2
 * @property string|null $caregiver_city
 * @property string|null $caregiver_state
 * @property string|null $caregiver_zip
 * @property float $payment_total
 * @property string $created_by
 * @property int|null $modified_by
 * @property string|null $caregiver_1099_payer
 * @property string|null $transmitted_at
 * @property int|null $transmitted_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Business $business
 * @property-read \App\Caregiver|null $caregiver
 * @property-read \App\Client $client
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver1099 newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver1099 newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver1099 query()
 * @mixin \Eloquent
 */
class Caregiver1099 extends BaseModel implements BelongsToBusinessesInterface
{
    use BelongsToOneBusiness;

    /**
     * 1099 Earnings threshold
     */
    const THRESHOLD = 600;

    protected $table = 'caregiver_1099s';
    protected $guarded = [];

    // Relations
    public function caregiver()
    {
        return $this->belongsTo(Caregiver::class);
    }

    public function client()
    {
        return $this->hasOne(Client::class, 'id', 'client_id');
    }

    public function business()
    {
        return $this->hasOne(Business::class, 'id', 'business_id');
    }

    public function client_address3()
    {
        return $this->client_city . ", " . $this->client_state . " " . $this->client_zip;
    }

    public function caregiver_address3()
    {
        return $this->caregiver_city . ", " . $this->caregiver_state . " " . $this->caregiver_zip;
    }

    /**
     * Determine if the payer is Ally.
     *
     * @return bool
     */
    public function isFromAlly(): bool
    {
        return $this->caregiver_1099_payer == Caregiver1099Payer::ALLY() ||
            $this->caregiver_1099_payer == Caregiver1099Payer::ALLY_LOCKED();
    }

    /**
     * Get a filled out copy of the caregiver 1099 PDF.
     * Note: This automatically aggregates the Ally 1099 totals.
     *
     * @param bool $maskPayerTin
     * @param bool $maskRecipientTin
     * @return Pdf
     */
    public function getFilledCaregiverPdf($maskPayerTin = true, $maskRecipientTin = true): Pdf
    {
        $caregiverTin = decrypt($this->caregiver_ssn);
        if ($this->uses_ein_number) {
            $caregiverTin = str_replace("-", "", $caregiverTin);
            $caregiverTin = substr($caregiverTin, 0, 2) . "-" . substr($caregiverTin, 2, 7);
        }

        if ($this->isFromAlly()) {
            // Ally is the payer
            $systemSettings = \DB::table('system_settings')->first();

            // Aggregate all Ally payer 1099s for the same year
            $total = $this->caregiver->caregiver1099s()
                ->where('year', $this->year)
                ->where('caregiver_1099_payer', Caregiver1099Payer::ALLY())
                ->get()
                ->bcsum('payment_total');

            return app(TaxDocumentPrinter::class)->create1099MiscCopyB(
                $this->year,
                $systemSettings->company_name,
                $systemSettings->company_address1,
                $systemSettings->company_address2,
                $systemSettings->company_city,
                $systemSettings->company_state,
                $systemSettings->company_zip,
                $systemSettings->company_ein,
                $caregiverTin,
                $this->caregiver_first_name . " " . $this->caregiver_last_name,
                $this->caregiver_address1,
                $this->caregiver_address2,
                $this->caregiver_city,
                $this->caregiver_state,
                $this->caregiver_zip,
                $total,
                $maskPayerTin,
                $maskRecipientTin
            );
        } else {
            // Client is the payer
            return app(TaxDocumentPrinter::class)->create1099MiscCopyB(
                $this->year,
                $this->client_first_name . " " . $this->client_last_name,
                $this->client_address1,
                $this->client_address2,
                $this->client_city,
                $this->client_state,
                $this->client_zip,
                $this->client_ssn ? decrypt($this->client_ssn) : '',
                $caregiverTin,
                $this->caregiver_first_name . " " . $this->caregiver_last_name,
                $this->caregiver_address1,
                $this->caregiver_address2,
                $this->caregiver_city,
                $this->caregiver_state,
                $this->caregiver_zip,
                $this->payment_total,
                $maskPayerTin,
                $maskRecipientTin
            );
        }
    }

    /**
     * Get a filled out copy of the caregiver 1099 PDF.
     * Note: This automatically aggregates the Ally 1099 totals.
     *
     * @param bool $maskPayerTin
     * @param bool $maskRecipientTin
     * @return Pdf
     */
    public function getFilledClientPdf($maskPayerTin = true, $maskRecipientTin = true): Pdf
    {
        $caregiverTin = decrypt($this->caregiver_ssn);
        if ($this->uses_ein_number) {
            $caregiverTin = str_replace("-", "", $caregiverTin);
            $caregiverTin = substr($caregiverTin, 0, 2) . "-" . substr($caregiverTin, 2, 7);
        }

        // Client is always the payer, client's do not have access to an Ally 1099
        return app(TaxDocumentPrinter::class)->create1099MiscCopyC(
            $this->year,
            $this->client_first_name . " " . $this->client_last_name,
            $this->client_address1,
            $this->client_address2,
            $this->client_city,
            $this->client_state,
            $this->client_zip,
            $this->client_ssn ? decrypt($this->client_ssn) : '',
            $caregiverTin,
            $this->caregiver_first_name . " " . $this->caregiver_last_name,
            $this->caregiver_address1,
            $this->caregiver_address2,
            $this->caregiver_city,
            $this->caregiver_state,
            $this->caregiver_zip,
            $this->payment_total,
            $maskPayerTin,
            $maskRecipientTin
        );
    }

    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************
    use \App\Traits\ScrubsForSeeding;

    /**
     * Get an array of scrubbed data to replace the original.
     *
     * @param \Faker\Generator $faker
     * @param bool $fast
     * @param null|\Illuminate\Database\Eloquent\Model $item
     * @return array
     */
    public static function getScrubbedData(\Faker\Generator $faker, bool $fast, ?\Illuminate\Database\Eloquent\Model $item): array
    {
        return [
            'client_first_name' => $faker->firstName,
            'client_last_name' => $faker->lastName,
            //'client_ssn' => '', This will get set via cleanEncrypted1099Data() function
            'client_address1' => $faker->streetAddress,
            'client_address2' => '',
            'client_city' => $faker->city,
            'client_state' => $faker->stateAbbr,
            'client_zip' => $faker->postcode,
            'caregiver_first_name' => $faker->firstName,
            'caregiver_last_name' => $faker->lastName,
            //'caregiver_ssn' => '', This will get set via cleanEncrypted1099Data() function
            'caregiver_address1' => $faker->streetAddress,
            'caregiver_address2' => '',
            'caregiver_city' => $faker->city,
            'caregiver_state' => $faker->stateAbbr,
            'caregiver_zip' => $faker->postcode,
            'created_by' => $faker->name,
        ];
    }
}
