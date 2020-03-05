<?php
namespace App\Http\Requests;

use App\Rules\ImageCropperUpload;
use App\Rules\ValidSSN;
use App\StatusAlias;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Caregiver;
use App\Rules\ValidEnum;
use App\Ethnicity;

class UpdateCaregiverRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        /** @var \App\Caregiver $caregiver */
        $caregiver = $this->route('caregiver');
        $aliases = StatusAlias::forAuthorizedChain()->forCaregivers()->pluck('id')->toArray();

        return [
            'firstname' => 'required|string|max:45',
            'lastname' => 'required|string|max:45',
            'email' => 'required_unless:no_email,1|nullable|email',
            'username' => ['required_unless:no_username,1', 'nullable', Rule::unique('users')->ignore(optional($caregiver)->id)],
            'date_of_birth' => 'nullable|date',
            'uses_ein_number' => 'required|boolean',
            'ssn' => [
                'nullable',
                new ValidSSN(),
            ],
            'password' => 'nullable|confirmed',
            'title' => 'required|string|max:32',
            'certification' => 'nullable|in:CNA,HHA,RN,LPN',
            'medicaid_id' => 'nullable|string|max:100',
            'gender' => 'nullable|in:M,F',
            'avatar' => [
                'nullable',
                new ImageCropperUpload()
            ],
            'application_date' => 'nullable|date',
            'orientation_date' => 'nullable|date',
            'referral_source_id' => 'nullable|exists:referral_sources,id',
            'status_alias_id' => 'nullable|in:' . join(',', $aliases),
            'smoking_okay' => 'nullable|boolean',
            'has_occ_acc' => 'nullable|boolean',
            'certificate_number' => 'nullable|string',
            'pets_dogs_okay' => 'nullable|boolean',
            'pets_cats_okay' => 'nullable|boolean',
            'pets_birds_okay' => 'nullable|boolean',
            'ethnicity' => ['nullable', new ValidEnum(Ethnicity::class)],
        ];
    }

    public function messages()
    {
        return [
            'email.required_unless' => 'The email is required unless you check the "No Email" box.',
            'username.required_unless' => 'The username is required unless you check the "Let Caregiver Choose" box.',
            'password.required_unless' => 'A password is required unless you check the "Let Caregiver Choose" box.',
            'username.unique' => 'This username is taken. Please use a different one.',
        ];
    }

    public function filtered()
    {
        $data = $this->validated();
        if (isset($data['ssn'])) {
            if (substr($data['ssn'], 0, 3) == '***') unset($data['ssn']);
        }
        if (isset($data['date_of_birth'])) {
            $data['date_of_birth'] = filter_date($data['date_of_birth']);
        }
        if (isset($data['application_date'])) {
            $data['application_date'] = filter_date($data['application_date']);
        }
        if (isset($data['orientation_date'])) {
            $data['orientation_date'] = filter_date($data['orientation_date']);
        }
        if ($this->input('no_username')) {
            // no need to change the username every time the caregiver is saved
            if (optional($this->route('caregiver'))->hasNoUsername()) {
                $data['username'] = $this->route('caregiver')->username;
            } else {
                $data['username'] = Caregiver::getAutoUsername();
            }
        }

        return $data;
    }
}