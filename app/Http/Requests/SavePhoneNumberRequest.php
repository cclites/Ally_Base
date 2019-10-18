<?php

namespace App\Http\Requests;

use App\Rules\PhonePossible;
use Illuminate\Foundation\Http\FormRequest;

class SavePhoneNumberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Evaluate in the controller
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'number' => ['required', new PhonePossible()],
            'extension' => 'nullable|numeric',
            'type' => 'required',
            'user_id' => 'sometimes|exists:users,id', // Only for office users or admins
            'notes' =>'nullable|string|max:255'
        ];
    }
}
