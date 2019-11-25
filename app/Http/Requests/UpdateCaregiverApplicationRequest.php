<?php
namespace App\Http\Requests;

class UpdateCaregiverApplicationRequest extends CaregiverApplicationStoreRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array_except(parent::rules(), [
           'caregiver_signature',
        ]);
    }
}
