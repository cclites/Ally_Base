<?php
namespace App\Http\Requests;

use App\StatusAlias;
use Illuminate\Support\Str;

class CreateCaregiverRequest extends UpdateCaregiverRequest
{
    public function rules()
    {
        $rules = [
            'username' => 'required|unique:users',
            'password' => 'required_unless:no_username,1|nullable|confirmed',
            'status_alias_id' => 'required',
        ] + parent::rules();

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
            'status_alias_id.*' => 'The status field is required.',
        ] + parent::messages();
    }

    public function filtered()
    {
        $data = [
            'password' => bcrypt($this->validated()['password'] ?? Str::random())
        ] + parent::filtered();

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