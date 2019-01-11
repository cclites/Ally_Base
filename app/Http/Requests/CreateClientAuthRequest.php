<?php
namespace App\Http\Requests;

use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Carbon\Carbon;

class CreateClientAuthRequest extends FormRequest
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
            'client_id' => 'required|exists:clients,id',
            'service_id' => 'required|exists:services,id',
            'payer_id' => 'nullable|numeric',
            'effective_start' => 'required|date',
            'effective_end' => 'required|date',
            'units' => 'required|numeric|min:0',
            'unit_type' => 'required|string',
            'period' => 'required|string',
            'notes' => 'nullable|string|max:255',
        ];
    }

    public function filtered() {
        $data = $this->validated();
        $data['effective_start'] = Carbon::parse($data['effective_start']);
        $data['effective_end'] = Carbon::parse($data['effective_end']);
        return $data;
    }

}
