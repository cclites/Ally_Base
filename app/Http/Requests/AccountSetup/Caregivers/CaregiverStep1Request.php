<?php

namespace App\Http\Requests\AccountSetup\Caregivers;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\PhonePossible;
use App\Caregiver;

class CaregiverStep1Request extends FormRequest
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
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email',
            'date_of_birth' => 'required|date',
            'ssn' => ['required', new ValidSSN()],
            'phone_number' => ['required', new PhonePossible()],
        ];
    }

    /**
     * Get the filtered request data.
     *
     * @return array
     */
    public function filtered() : array
    {
        $data = $this->validated();
        $data['date_of_birth'] = filter_date($data['date_of_birth']);
        unset($data['phone_number']);
        $data['setup_status'] = Caregiver::SETUP_CONFIRMED_PROFILE;
        return $data;
    }
}
