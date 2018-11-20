<?php
namespace App\Http\Requests;

use App\Rules\Avatar;
use App\Rules\ValidSSN;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCaregiverRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required_unless:no_email,1|nullable|email',
            'username' => [
                'required',
                Rule::unique('users')->ignore($this->route('caregiver'), 'id'),
            ],
            'date_of_birth' => 'nullable|date',
            'ssn' => [
                'nullable',
                new ValidSSN(),
            ],
            'password' => 'nullable|confirmed',
            'title' => 'required',
            'medicaid_id' => 'nullable',
            'gender' => 'nullable|in:M,F',
            'avatar' => [
                'nullable',
                new Avatar()
            ],
        ];
    }

    public function messages()
    {
        return [
            'email.required_unless' => 'The email is required unless you check the "No Email" box.',
            'username.unique' => 'This username is taken. Please use a different one.',
        ];
    }

    public function filtered()
    {
        $data = $this->validated();
        if ($data['date_of_birth']) $data['date_of_birth'] = filter_date($data['date_of_birth']);

        return $data;
    }
}