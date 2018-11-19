<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\ClientType;

class CreateQuestionRequest extends BusinessRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'question' => 'required|string|max:255',
            'client_type' => 'nullable|in:' . implode(',', ClientType::all()),
            'required' => 'nullable|boolean',
        ];
    }
}
