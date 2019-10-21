<?php

namespace App\Http\Requests\AccountSetup\Clients;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\PhonePossible;
use App\Rules\ValidSSN;
use App\Client;

class ClientStep1Request extends FormRequest
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
            'accepted_terms' => 'accepted',
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email',
            'date_of_birth' => 'required|date',
            'ssn' => ['required', new ValidSSN()],
            'phone_number' => ['required', new PhonePossible()],
        ];
    }

    /**
     * Get the validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'accepted_terms.accepted' => 'You must accept the terms of service by checking the box.'
        ];
    }

    /**
     * Get the filtered request data.
     *
     * @param \App\Client $client
     * @return array
     */
    public function filtered(Client $client) : array
    {
        $data = $this->validated();
        $data['date_of_birth'] = filter_date($data['date_of_birth']);
        unset($data['accepted_terms']);
        unset($data['phone_number']);
        if ($client->agreement_status != Client::SIGNED_PAPER) {
            // don't update agreement status if use has been set as 'signed paper agreement'
            $data['agreement_status'] = Client::SIGNED_ELECTRONICALLY;
        }
        $data['setup_status'] = Client::SETUP_ACCEPTED_TERMS;
        return $data;
    }
}
