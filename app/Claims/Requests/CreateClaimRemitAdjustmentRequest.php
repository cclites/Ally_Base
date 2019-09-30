<?php

namespace App\Claims\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateClaimRemitAdjustmentRequest extends FormRequest
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
        return [
        ];
    }

    /**
     * Get the filtered validated data.
     *
     * @return array
     */
    public function filtered()
    {
        $data = $this->validated();
        return $data;
    }
}
