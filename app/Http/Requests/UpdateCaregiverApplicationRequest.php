<?php
namespace App\Http\Requests;

use Illuminate\Support\Arr;

class UpdateCaregiverApplicationRequest extends CaregiverApplicationStoreRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return Arr::except(parent::rules(), [
           'caregiver_signature',
        ]);
    }
}
