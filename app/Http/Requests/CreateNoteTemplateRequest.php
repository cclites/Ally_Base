<?php
namespace App\Http\Requests;

class CreateNoteTemplateRequest extends BusinessRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'short_name' => 'required|string|max:32',
            'active' => 'required|boolean',
            'note' => 'required|string',
        ];
    }
}
