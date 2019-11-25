<?php
namespace App\Http\Requests;

use App\PhoneNumber;
use App\Rules\PhonePossible;
use App\Rules\ValidSSN;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class CaregiverApplicationStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => 'required|string',
            'middle_initial' => 'nullable|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
            'date_of_birth' => 'nullable|date',
            'ssn' => ['nullable', new ValidSSN()],
            'address' => 'required|string|max:255',
            'address_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|size:2',
            'zip' => 'required|string|min:5',
            'cell_phone' => ['nullable', new PhonePossible()],
            'cell_phone_provider' => 'nullable|string|max:60',
            'home_phone' => ['nullable', new PhonePossible()],
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:100',
            'worked_here_before' => 'boolean',
            'worked_before_location' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:200',
            'certification' => 'nullable|string',
            'license_number' => 'nullable|string',
            'training_school' => 'nullable|string',
            'preferred_start_date' => 'nullable|date',
            'preferred_days' => 'nullable|array',
            'preferred_times' => 'nullable|array',
            'preferred_shift_length' => 'nullable|array',
//            'work_weekends' => 'boolean',
            'travel_radius' => 'nullable|integer',
            'vehicle' => 'nullable|string',
            'dui' => 'boolean',
            'reckless_driving' => 'boolean',
            'moving_violation' => 'boolean',
            'moving_violation_count' => 'nullable|integer',
            'accidents' => 'boolean',
            'accident_count' => 'nullable|integer',
            'driving_violations_desc' => 'nullable|string',
            'felony_conviction' => 'boolean',
            'theft_conviction' => 'boolean',
            'drug_conviction' => 'boolean',
            'violence_conviction' => 'boolean',
            'criminal_history_desc' => 'nullable|string',
            'currently_injured' => 'boolean',
            'previously_injured' => 'boolean',
            'lift_25_lbs' => 'boolean',
            'workmans_comp' => 'boolean',
            'workmans_comp_dates' => 'nullable|string|max:255',
            'injury_status_desc' => 'nullable|string',
            'employer_1_name' => 'nullable|string|max:150',
            'employer_1_city' => 'nullable|string|max:60',
            'employer_1_state' => 'nullable|string|size:2',
            'employer_1_approx_start_date' => 'nullable|date',
            'employer_1_approx_end_date' => 'nullable|date',
            'employer_1_phone' => ['nullable', new PhonePossible()],
            'employer_1_job_title' => 'nullable|string|max:150',
            'employer_1_supervisor_name' => 'nullable|string|max:100',
            'employer_1_reason_for_leaving' => 'nullable|string',
            'employer_2_name' => 'nullable|string|max:150',
            'employer_2_city' => 'nullable|string|max:60',
            'employer_2_state' => 'nullable|string|size:2',
            'employer_2_approx_start_date' => 'nullable|date',
            'employer_2_approx_end_date' => 'nullable|date',
            'employer_2_phone' => ['nullable', new PhonePossible()],
            'employer_2_job_title' => 'nullable|string|max:150',
            'employer_2_supervisor_name' => 'nullable|string|max:100',
            'employer_2_reason_for_leaving' => 'nullable|string',
            'employer_3_name' => 'nullable|string|max:150',
            'employer_3_city' => 'nullable|string|max:60',
            'employer_3_state' => 'nullable|string|size:2',
            'employer_3_approx_start_date' => 'nullable|date',
            'employer_3_approx_end_date' => 'nullable|date',
            'employer_3_phone' => ['nullable', new PhonePossible()],
            'employer_3_job_title' => 'nullable|string|max:150',
            'employer_3_supervisor_name' => 'nullable|string|max:100',
            'employer_3_reason_for_leaving' => 'nullable|string',
            'reference_1_name' => 'nullable|string|max:100',
            'reference_1_phone' => ['nullable', new PhonePossible()],
            'reference_1_relationship' => 'nullable|string|max:100',
            'reference_2_name' => 'nullable|string|max:100',
            'reference_2_phone' => ['nullable', new PhonePossible()],
            'reference_2_relationship' => 'nullable|string|max:100',
            'reference_3_name' => 'nullable|string|max:100',
            'reference_3_phone' => ['nullable', new PhonePossible()],
            'reference_3_relationship' => 'nullable|string|max:100',
            'heard_about' => 'nullable|array',
            'caregiver_signature' => 'required',
            'has_cell_phone' => 'required|boolean',
            'has_smart_phone' => 'required|boolean',
            'can_text' => 'required|boolean',
        ];
    }

    public function filtered() {
        $data = $this->validated();

        // Handle arrays
        $data['preferred_days'] = is_array($data['preferred_days']) ? implode(',', $data['preferred_days']) : '';
        $data['preferred_times'] = is_array($data['preferred_times']) ? implode(',', $data['preferred_times']) : '';
        $data['preferred_shift_length'] = is_array($data['preferred_shift_length']) ? implode(',', $data['preferred_shift_length']) : '';
        $data['heard_about'] = is_array($data['heard_about']) ? implode(',', $data['heard_about']) : '';

        // Handle dates
        $data['date_of_birth'] = Carbon::parse($data['date_of_birth']);
        $data['preferred_start_date'] = Carbon::parse($data['preferred_start_date']);
        $data['employer_1_approx_start_date'] = Carbon::parse($data['employer_1_approx_start_date']);
        $data['employer_1_approx_end_date'] = Carbon::parse($data['employer_1_approx_end_date']);
        $data['employer_2_approx_start_date'] = Carbon::parse($data['employer_2_approx_start_date']);
        $data['employer_2_approx_end_date'] = Carbon::parse($data['employer_2_approx_end_date']);
        $data['employer_3_approx_start_date'] = Carbon::parse($data['employer_3_approx_start_date']);
        $data['employer_3_approx_end_date'] = Carbon::parse($data['employer_3_approx_end_date']);

        // Handle phone numbers
        $phoneNumberFields = [
            'cell_phone',
            'home_phone',
            'employer_1_phone',
            'employer_2_phone',
            'employer_3_phone',
            'reference_1_phone',
            'reference_2_phone',
            'reference_3_phone',
        ];
        foreach($phoneNumberFields as $field) {
            if ($number = $data[$field] ?? null) {
                $phone = new PhoneNumber();
                $phone->input($number);
                $data[$field] = $phone->national_number;
            }
        }

        return $data;
    }
}
