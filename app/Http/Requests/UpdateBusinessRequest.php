<?php

namespace App\Http\Requests;

use App\Rules\ValidTimezoneOrOffset;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessRequest extends FormRequest
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
            'logo' => 'nullable|image|max:4000',
            'scheduling' => 'required|bool',
            'auto_confirm' => 'required|bool',
            'ask_on_confirm' => 'required|bool',
            'location_exceptions' => 'required|bool',
            'timesheet_exceptions' => 'required|bool',
            'mileage_rate' => 'required|numeric',
            'calendar_default_view' => 'required',
            'calendar_caregiver_filter' => 'required|in:all,unassigned',
            'phone1' => 'nullable|string',
            'phone2' => 'nullable|string',
            'address1' => 'nullable|string',
            'address2' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'zip' => 'nullable|string',
            'country' => 'nullable|string',
            'timezone' => ['required', new ValidTimezoneOrOffset()],
        ];
    }
}
