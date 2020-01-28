<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserAdminNoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return is_admin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

            'subject_user_id' => 'required|exists:users,id',
            'body'            => 'required|string',
        ];
    }

    /**
     * Filter the request data for processing.
     *
     * @return array
     */
    public function filtered(): array
    {
        $data = $this->validated();
        $data['creator_user_id'] = auth()->user()->id;
        return $data;
    }
}
