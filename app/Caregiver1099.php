<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use App\Caregiver;
use App\Client;
use App\Business;
use mikehaertl\pdftk\Pdf;

class Caregiver1099 extends BaseModel
{
    /**
     * 1099 Earnings threshold
     */
    const THRESHOLD = 600;

    protected $table = 'caregiver_1099s';
    protected $guarded = [];

    // Relations
    public function caregiver(){
        return $this->belongsTo(Caregiver::class);
    }

    public function client(){
        return $this->hasOne(Client::class, 'id', 'client_id');
    }

    public function business(){
        return $this->hasOne(Business::class, 'id', 'business_id');
    }

    public function client_address3(){
        return $this->client_city . ", " . $this->client_state . " " . $this->client_zip;
    }

    public function caregiver_address3(){
        return $this->caregiver_city . ", " . $this->caregiver_state . " " . $this->caregiver_zip;
    }

    public function getFilledCaregiverPdf($maskPayerSsn = true, $maskRecipientSsn = true) : Pdf
    {
        $this->load("client");

        $systemSettings = \DB::table('system_settings')->first();

        $pdf = new Pdf('../resources/pdf_forms/caregiver1099s/' . $this->year . '/cg-1099-b-2.pdf');

        $payerTin = $this->client_ssn ? decrypt($this->client_ssn) : '';
        $payerName = $this->client_first_name . " " . $this->client_last_name;
        $clAddress2 = $this->client_address2 ? $this->client_address2 . "\n" : '';
        $caAddress2 = $this->caregiver_address2 ? ", " . $this->caregiver_address2 : '';
        $payerAddress = $payerName . "\n" . $this->client_address1 . "\n" . $clAddress2 . $this->client_address3();
        $paymentTotal = $this->caregiver_1099_amount ? $this->caregiver_1099_amount : $this->payment_total;
        $caregiverTin = decrypt($this->caregiver_ssn);

        if ($this->uses_ein_number) {
            $caregiverTin = str_replace("-", "", $caregiverTin);
            $caregiverTin = substr($caregiverTin, 0, 2) . "-" . substr($caregiverTin, 2, 7);
        }

        if ($this->caregiver_1099_payer == 'ally') {
            $payerName = $this->client_first_name;
            $payerAddress3 = $systemSettings->company_city . ", " . $systemSettings->company_state . " " . $systemSettings->company_zip;
            $clAddress2 = $systemSettings->company_address2 ? $systemSettings->company_address2 . "\n" : '';
            $payerAddress = $payerName . "\n" . $systemSettings->company_address1 . "\n" . $clAddress2 . $payerAddress3;
        }

        if ($maskPayerSsn) {
            $payerTin = '**-*******';
        }

        if ($maskRecipientSsn) {
            $caregiverTin = '***-**-' . substr($caregiverTin . "", -4);
        }

        $pdf->fillForm([
            /** COPY B **/
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_1[0]' => strtoupper($payerAddress),
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_2[0]' => $payerTin, //payers tin
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_3[0]' => $caregiverTin, //recipient tin
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_4[0]' => strtoupper($this->caregiver_first_name . " " . $this->caregiver_last_name), //recipient name
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_5[0]' => strtoupper($this->caregiver_address1 . $caAddress2), //recipient street address
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_6[0]' => strtoupper($this->caregiver_address3()), //recipient city, state, zip
            'topmostSubform[0].CopyB[0].RightCol[0].f2_14[0]' => $paymentTotal,

            /** COPY 2 **/
            'topmostSubform[0].Copy2[0].LeftColumn[0].f2_1[0]' => strtoupper($payerAddress),
            'topmostSubform[0].Copy2[0].LeftColumn[0].f2_2[0]' => $payerTin, //payers tin
            'topmostSubform[0].Copy2[0].LeftColumn[0].f2_3[0]' => $caregiverTin, //recipient tin
            'topmostSubform[0].Copy2[0].LeftColumn[0].f2_4[0]' => strtoupper($this->caregiver_first_name . " " . $this->caregiver_last_name), //recipient name
            'topmostSubform[0].Copy2[0].LeftColumn[0].f2_5[0]' => strtoupper($this->caregiver_address1 . $caAddress2), //recipient street address
            'topmostSubform[0].Copy2[0].LeftColumn[0].f2_6[0]' => strtoupper($this->caregiver_address3()), //recipient city, state, zip
            'topmostSubform[0].Copy2[0].RightColumn[0].f2_14[0]' => $paymentTotal,
        ])->execute();

        return $pdf;
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
    public static function getScrubbedData(\Faker\Generator $faker, bool $fast, ?\Illuminate\Database\Eloquent\Model $item) : array
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
