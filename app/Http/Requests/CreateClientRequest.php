<?php
namespace App\Http\Requests;

use App\Client;
use App\Http\Controllers\Business\StatusAliasController;
use App\Rules\ValidSSN;
use App\StatusAlias;

class CreateClientRequest extends BusinessRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'firstname' => 'required|string|max:45',
            'lastname' => 'required|string|max:45',
            'email' => 'required_unless:no_email,1|nullable|email',
            'username' => 'required|unique:users',
            'password' => 'required_unless:no_username,1|nullable|confirmed',
            'date_of_birth' => 'nullable',
            'business_fee' => 'nullable|numeric',
            'client_type' => 'required',
            'ssn' => ['nullable', new ValidSSN()],
            'agreement_status' => 'required',
            'gender' => 'nullable|in:M,F',
            'status_alias_id' => 'required',
        ];

        if ($this->input('no_username')) {
            // Clear the username field when let user choose option is selected
            // so the username can be dummy filled on the UI as the user's email
            // and not actually update or validate unique when creating the user.
            unset($rules['username']);
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'email.required_unless' => 'The email is required unless you check the "No Email" box.',
            'username.required_unless' => 'The username is required unless you check the "Let Client Choose" box.',
            'password.required_unless' => 'A password is required unless you check the "Let Client Choose" box.',
            'username.unique' => 'This username is taken. Please use a different one.',
            'status_alias_id.*' => 'The status field is required.',
        ];
    }

    public function filtered()
    {
        $data = $this->validated();
        if ($data['date_of_birth']) $data['date_of_birth'] = filter_date($data['date_of_birth']);
        if (substr($data['ssn'], 0, 3) == '***') unset($data['ssn']);
        $data['password'] = bcrypt($data['password'] ?? str_random());

        if ($data['status_alias_id'] == -1) {
            // inactive
            $data['active'] = 0;
            $data['status_alias_id'] = null;
        } else if ($data['status_alias_id'] == 0) {
            // active
            $data['active'] = 1;
            $data['status_alias_id'] = null;
        } else {
            // status alias
            $statusAlias = StatusAlias::find($data['status_alias_id']);
            if (empty($statusAlias)) {
                $data['active'] = 1;
                $data['status_alias_id'] = null;
            } else {
                $data['active'] = $statusAlias->active;
                $data['status_alias_id'] = $statusAlias->id;
            }
        }

        return $data;
    }
}
