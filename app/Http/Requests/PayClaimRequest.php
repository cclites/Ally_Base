<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class PayClaimRequest extends FormRequest
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
            'payed_at' => 'required|date',
            'amount' => 'required|numeric|between:0,99999.99',
            'reference' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
        ];
    }
    
    /**
     * Get the filtered request data.
     *
     * @return array
     */
    public function filtered()
    {
        $data = $this->validated();
        $data['payed_at'] = Carbon::parse($data['payed_at'])->format('Y-m-d');
        return $data;
    }
}