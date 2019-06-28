<?php
namespace App\Http\Requests;

use App\Billing\ClientAuthorization;
use App\Client;
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
            'service_auth_id' => 'nullable|string|max:255',
            'service_id' => 'required|exists:services,id',
            'effective_start' => 'required|date',
            'effective_end' => 'required|date',
            'units' => 'required_unless:period,specific_days|numeric|min:0',
            'unit_type' => 'required|string',
            'period' => 'required|string|in:' . implode(',', ClientAuthorization::allPeriods()),
            'notes' => 'nullable|string|max:255',
            'week_start' => 'required_if:period,weekly|numeric|in:0,1,2,3,4,5,6',
            'sunday' => 'required_if:period,specific_days|numeric|min:0',
            'monday' => 'required_if:period,specific_days|numeric|min:0',
            'tuesday' => 'required_if:period,specific_days|numeric|min:0',
            'wednesday' => 'required_if:period,specific_days|numeric|min:0',
            'thursday' => 'required_if:period,specific_days|numeric|min:0',
            'friday' => 'required_if:period,specific_days|numeric|min:0',
            'saturday' => 'required_if:period,specific_days|numeric|min:0',
        ];
    }

    public function filtered() {
        $data = $this->validated();
        $data['effective_start'] = Carbon::parse($data['effective_start']);
        $data['effective_end'] = Carbon::parse($data['effective_end']);
        if ($data['period'] == ClientAuthorization::PERIOD_SPECIFIC_DAYS) {
            $data['units'] = 0.0;
        } else {
            unset($data['sunday']);
            unset($data['monday']);
            unset($data['tuesday']);
            unset($data['wednesday']);
            unset($data['thursday']);
            unset($data['friday']);
            unset($data['saturday']);
        }
        return $data;
    }

}
