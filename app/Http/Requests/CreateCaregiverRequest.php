<?php
namespace App\Http\Requests;

class CreateCaregiverRequest extends UpdateCaregiverRequest
{
    public function rules()
    {
        $rules = [
            'username' => 'required|unique:users',
            'password' => 'required_unless:no_username,1|nullable|confirmed',
        ] + parent::rules();

        if ($this->input('no_username')) {
            // Clear the username field when let user choose option is selected
            // so the username can be dummy filled on the UI as the user's email
            // and not actually update or validate unique when creating the user.
            unset($rules['username']);
        }

        return $rules;
    }

    public function filtered()
    {
        return [
            'password' => bcrypt($this->validated()['password'] ?? str_random())
        ] + parent::filtered();
    }
}