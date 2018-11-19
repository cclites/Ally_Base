<?php
namespace App\Http\Requests;

use App\Client;

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
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required_unless:no_email,1|nullable|email',
            'username' => 'required|unique:users',
            'date_of_birth' => 'nullable',
            'business_fee' => 'nullable|numeric',
            'client_type' => 'required',
            'ssn' => ['nullable', new ValidSSN()],
            'onboard_status' => 'required',
            'gender' => 'nullable|in:M,F',
            'password' => 'nullable|string',
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
        if (substr($data['ssn'], 0, 3) == '***') unset($data['ssn']);
        $data['password'] = bcrypt($data['password'] ?: str_random());
        if (empty($data['email'])) $data['email'] = (new Client())->getAutoEmail();

        return $data;
    }
}
