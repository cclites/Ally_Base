<?php

namespace App\Http\Requests;

use App\Rules\ValidTimezoneOrOffset;
use Crypt;

class UpdateBusinessRequest extends BusinessRequest
{
    protected $preserveValidated = true;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'logo' => 'nullable|image|max:4000',
            'scheduling' => 'required|bool',
            'auto_confirm' => 'required|bool',
            'ask_on_confirm' => 'required|bool',
            'allows_manual_shifts' => 'required|bool',
            'location_exceptions' => 'required|bool',
            'timesheet_exceptions' => 'required|bool',
            'require_signatures' => 'required|bool',
            'mileage_rate' => 'required|numeric',
            'shift_rounding_method' => 'required|in:none,shift,individual',
            'calendar_default_view' => 'required',
            'calendar_caregiver_filter' => 'required|in:all,unassigned',
            'calendar_remember_filters' => 'required|bool',
            'calendar_next_day_threshold' => 'required|date_format:H:i:s',
            'phone1' => 'nullable|string',
            'phone2' => 'nullable|string',
            'address1' => 'nullable|string',
            'address2' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'zip' => 'nullable|string',
            'country' => 'nullable|string',
            'timezone' => ['required', new ValidTimezoneOrOffset()],
            'co_mileage' => 'required|bool',
            'co_injuries' => 'required|bool',
            'co_comments' => 'required|bool',
            'co_expenses' => 'required|bool',
            'co_issues' => 'required|bool',
            'co_signature' => 'required|bool',
            'co_caregiver_signature' => 'required|bool',
            'ein' => 'nullable',
            'medicaid_id' => 'nullable',
            'medicaid_npi_number' => 'nullable',
            'medicaid_npi_taxonomy' => 'nullable',
            'allow_client_confirmations' => 'required|bool',
            'auto_confirm_modified' => 'required|bool',
            'shift_confirmation_email' => 'required|bool',
            'sce_shifts_in_progress' => 'required|bool',
            'charge_diff_email' => 'required|bool',
            'auto_append_hours' => 'required|bool',
            'auto_confirm_unmodified_shifts' => 'required|bool',
            'auto_confirm_verified_shifts' => 'required|bool',
            'enable_client_onboarding' => 'required|bool',
            'hha_username' => 'nullable|string|max:255',
            'hha_password' => 'nullable',
            'tellus_username' => 'nullable|string|max:255',
            'tellus_password' => 'nullable',
        ];
    }

    public function messages() {
        return [
            'calendar_next_day_threshold.date_format' => 'The next day threshold must be in 24-hour format as HH:MM:SS',
        ];
    }

    public function filtered()
    {
        $data = $this->validated();
        if ($data['auto_confirm'] == 1) {
            // turn off related other confirm settings
            $data['allow_client_confirmations'] = 0;
            $data['auto_confirm_modified'] = 0;
            $data['shift_confirmation_email'] = 0;
            $data['charge_diff_email'] = 0;
            $data['auto_append_hours'] = 0;
            $data['auto_confirm_unmodified_shifts'] = 0;
        }

        if ($data['hha_password'] == '********') {
            unset($data['hha_password']);
        }
        if (isset($data['hha_password'])) {
            $data['hha_password'] = Crypt::encrypt($data['hha_password']);
        }

        if ($data['tellus_password'] == '********') {
            unset($data['tellus_password']);
        }
        if (isset($data['tellus_password'])) {
            $data['tellus_password'] = Crypt::encrypt($data['tellus_password']);
        }

        return $data;
    }
}
