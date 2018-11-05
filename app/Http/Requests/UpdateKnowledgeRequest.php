<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKnowledgeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $knowledge = $this->route('knowledge');

        return [
            'type' => 'required|in:faq,tutorial,resource',
            'title' => 'required|string|max:500',
            'slug' => [
                'nullable',
                'max:500',
                Rule::unique('knowledge')->ignore($knowledge->id),
            ],
            'body' => 'nullable|string',
            'youtube_id' => 'nullable|string|max:255',
            'attachments' => 'array|max:4',
            'video_attachment_id' => 'nullable|exists:attachments,id'
        ];
    }
}
