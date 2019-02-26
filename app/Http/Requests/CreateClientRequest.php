<?php
namespace App\Http\Requests;

use App\Client;
use App\Rules\ValidSSN;

class CreateClientRequest extends BusinessRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'firstname' => 'required|string|max:45',
            'lastname' => 'required|string|max:45',
            'email' => 'required_unless:no_email,1|nullable|email',
            'username' => 'required_unless:no_username,1|nullable|unique:users',
            'password' => 'required_unless:no_username,1|nullable|confirmed',
            'date_of_birth' => 'nullable',
            'business_fee' => 'nullable|numeric',
            'client_type' => 'required',
            'ssn' => ['nullable', new ValidSSN()],
            'agreement_status' => 'required',
            'gender' => 'nullable|in:M,F',
        ];
    }

    public function messages()
    {
        return [
            'email.required_unless' => 'The email is required unless you check the "No Email" box.',
            'username.required_unless' => 'The username is required unless you check the "Let Client Choose" box.',
            'password.required_unless' => 'A password is required unless you check the "Let Client Choose" box.',
            'username.unique' => 'This username is taken. Please use a different one.',
        ];
    }

    public function filtered()
    {
        $data = $this->validated();
        if ($data['date_of_birth']) $data['date_of_birth'] = filter_date($data['date_of_birth']);
        if (substr($data['ssn'], 0, 3) == '***') unset($data['ssn']);
        $data['password'] = bcrypt($data['password'] ?? str_random());

        return $data;
    }
}
