<?php


namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmailTemplateRequest extends BusinessRequest
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
     * @return array
     */
    public function rules()
    {
       return [
            'greeting' => 'required',
            'body'=> 'required',
            'type' => 'required',
            'business_id' => 'required',
        ];

    }

    /**
     * Get the filtered data.
     *
     * @return array
     */
    public function filtered()
    {
        $data = $this->validated();
        return $data;
    }
}