<?php

namespace App;

use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * App\ClientOnboarding
 *
 * @property int $id
 * @property int $client_id
 * @property string|null $middle_initial
 * @property string|null $address
 * @property string|null $phone_number
 * @property int|null $facility
 * @property string|null $facility_instructions
 * @property string|null $primary_conditions
 * @property string|null $service_reasons
 * @property string|null $service_goals
 * @property string|null $allergies
 * @property string|null $medical_equipment
 * @property string|null $height
 * @property string|null $weight
 * @property string|null $physician_name
 * @property string|null $physician_phone
 * @property string|null $physician_address
 * @property string|null $pharmacy_name
 * @property string|null $pharmacy_phone
 * @property string|null $pharmacy_address
 * @property int|null $hospice_care
 * @property string|null $hospice_office_location
 * @property string|null $hospice_case_manager
 * @property string|null $hospice_phone
 * @property int|null $dnr
 * @property string|null $dnr_location
 * @property string|null $ec_name
 * @property string|null $ec_address
 * @property string|null $ec_phone_number
 * @property string|null $ec_email
 * @property string|null $ec_relationship
 * @property int|null $ec_poa
 * @property string|null $secondary_ec_name
 * @property string|null $secondary_ec_address
 * @property string|null $secondary_ec_phone_number
 * @property string|null $secondary_ec_email
 * @property string|null $secondary_ec_relationship
 * @property int|null $secondary_ec_poa
 * @property int|null $emp_leave_region
 * @property string|null $emp_with_who_where
 * @property int|null $emp_remain_home
 * @property int|null $emp_shelter
 * @property string|null $emp_shelter_type
 * @property string|null $emp_shelter_address
 * @property int|null $emp_shelter_registration_assistance
 * @property string|null $emp_evacuation_responsible_party
 * @property int|null $emp_caregiver_required
 * @property string|null $cg_gender_pref
 * @property string|null $cg_attire_pref
 * @property int|null $pets
 * @property string|null $pets_description
 * @property int|null $cg_pet_assistance
 * @property int|null $transportation
 * @property string|null $transportation_vehicle
 * @property string|null $requested_start_at
 * @property string|null $requested_schedule
 * @property string|null $relation_to_intake_party
 * @property string|null $intake_pdf
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\OnboardingActivity[] $activities
 * @property-read \App\Client $client
 * @property-read \App\Signature $signature
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereAllergies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereCgAttirePref($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereCgGenderPref($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereCgPetAssistance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereDnr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereDnrLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereEcAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereEcEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereEcName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereEcPhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereEcPoa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereEcRelationship($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereEmpCaregiverRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereEmpEvacuationResponsibleParty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereEmpLeaveRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereEmpRemainHome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereEmpShelter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereEmpShelterAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereEmpShelterRegistrationAssistance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereEmpShelterType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereEmpWithWhoWhere($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereFacility($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereFacilityInstructions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereHospiceCare($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereHospiceCaseManager($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereHospiceOfficeLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereHospicePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereIntakePdf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereMedicalEquipment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereMiddleInitial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding wherePets($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding wherePetsDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding wherePharmacyAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding wherePharmacyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding wherePharmacyPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding wherePhysicianAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding wherePhysicianName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding wherePhysicianPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding wherePrimaryConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereRelationToIntakeParty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereRequestedSchedule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereRequestedStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereSecondaryEcAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereSecondaryEcEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereSecondaryEcName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereSecondaryEcPhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereSecondaryEcPoa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereSecondaryEcRelationship($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereServiceGoals($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereServiceReasons($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereTransportation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereTransportationVehicle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientOnboarding whereWeight($value)
 * @mixin \Eloquent
 */
class ClientOnboarding extends BaseModel
{
    protected $guarded = ['id'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function activities()
    {
        return $this->belongsToMany(OnboardingActivity::class, 'client_onboarding_activities')
            ->withPivot('assistance_level');
    }

    public function signature()
    {
        return $this->morphOne(Signature::class, 'signable');
    }

    public function createIntakePdf()
    {
        $this->load('client', 'signature', 'activities');
        $pdf = PDF::loadView('business.clients.onboarding_doc', ['onboarding' => $this, 'override_ally_logo' => $this->client->business->logo]);
        $dir = storage_path('app/documents/');
        if (! File::exists($dir)) {
            File::makeDirectory($dir, 493, true);
        }
        $filename = str_slug($this->client->id . ' ' . $this->client->name . ' Intake') . '.pdf';
        $filePath = $dir . '/' . $filename;
        if (config('app.env') == 'local') {
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
        }
        $response = $pdf->save($filePath);

        if ($response) {
            DB::transaction(function () use ($response, $filePath) {
                $this->update(['intake_pdf' => str_after($filePath, 'storage/')]);
                $this->client->documents()->create([
                    'filename' => File::basename($filePath),
                    'original_filename' => File::basename($filePath),
                    'description' => 'Client Intake Document'
                ]);
            });
        }
    }
}
