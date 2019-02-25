<?php
namespace App\Http\Requests;

class CreateCaregiverRequest extends UpdateCaregiverRequest
{
    public function rules()
    {
        return [
            'username' => 'required_unless:no_username,1|nullable|unique:users',
            'password' => 'required_unless:no_username,1|nullable|confirmed',
        ] + parent::rules();
    }

    public function filtered()
    {
        return [
            'password' => bcrypt($this->validated()['password'] ?? str_random())
        ] +parent::filtered();
    }
}