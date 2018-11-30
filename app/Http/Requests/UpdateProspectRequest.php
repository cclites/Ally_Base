<?php

namespace App\Http\Requests;

use App\PhoneNumber;
use App\Rules\PhonePossible;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProspectRequest extends BusinessRequest
{
    protected $dates = ['date_of_birth', 'last_contacted', 'initial_call_date'];

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
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'nullable|email',
            'client_type' => 'required',
            'date_of_birth' => 'nullable|date',
            'phone' => ['nullable', new PhonePossible()],
            'address1' => 'nullable|string',
            'address2' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string|size:2',
            'zip' => 'nullable|string|max:10',
            'country' => 'nullable|string|size:2',
            'referred_by' => 'nullable|string',
            'last_contacted' => 'nullable|date',
            'initial_call_date' => 'nullable|date',
            'had_initial_call' => 'boolean',
            'had_assessment_scheduled' => 'boolean',
            'had_assessment_performed' => 'boolean',
            'needs_contract' => 'boolean',
            'expecting_client_signature' => 'boolean',
            'needs_payment_info' => 'boolean',
            'ready_to_schedule' => 'boolean',
            'closed_loss' => 'boolean',
            'closed_win' => 'boolean',
            'referral_source_id' => 'nullable|numeric',
        ];
    }

    public function filtered()
    {
        $data = $this->validated();

        // Format dates
        foreach($this->dates as $field) {
            if (isset($data[$field])) {
                $data[$field] = Carbon::parse($data[$field])->toDateString();
            }
        }

        // Format phone
        if (isset($data['phone'])) {
            $phone = (new PhoneNumber)->input($data['phone']);
            $data['phone'] = $phone->number();
        }

        return $data;
    }
}
