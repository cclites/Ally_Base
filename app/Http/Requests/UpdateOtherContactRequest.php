<?php

namespace App\Http\Requests;

use App\PhoneNumber;
use App\Rules\PhonePossible;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOtherContactRequest extends BusinessRequest
{  
    /**
     * The fields to cast to Carbon dates
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

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
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'nullable|email',
            'title' => 'nullable|string',
            'company' => 'nullable|string',
            'general_notes' => 'nullable|string',
            'phone' => ['nullable', new PhonePossible()],
            'address1' => 'nullable|string',
            'address2' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string|size:2',
            'zip' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:3',
        ];
    }
    
    /**
     * Format and return the validated fields
     *
     * @return array
     */
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
