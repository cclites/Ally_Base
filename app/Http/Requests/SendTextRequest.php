<?php
namespace App\Http\Requests;

class SendTextRequest extends BusinessRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'message' => 'required|string|min:5',
            'recipients' => 'array',
            'recipients.*' => 'integer',
            'can_reply' => 'boolean',
        ];
    }
}
