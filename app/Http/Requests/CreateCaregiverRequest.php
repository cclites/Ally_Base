<?php
namespace App\Http\Requests;

class CreateCaregiverRequest extends UpdateCaregiverRequest
{
    public function rules()
    {
        return [
            'username' => 'required|unique:users',
            'password' => 'nullable|string',
        ] + parent::rules();
    }

    public function filtered()
    {
        return [
            'password' => bcrypt($this->validated()['password'] ?? str_random())
        ] +parent::filtered();
    }
}