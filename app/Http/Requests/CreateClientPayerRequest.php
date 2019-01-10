<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;
use App\Billing\ClientPayer;

class CreateClientPayerRequest extends FormRequest
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
            'payer_id' => [
                'nullable',
                Rule::exists('payers', 'id')->where(function ($query) {
                    $query->where('chain_id', $this->route('client')->business->chain_id);
                }),
                Rule::unique('client_payers')->where(function ($query) {
                    $query->where('client_id', request()->client->id);
                }),
            ],
            'policy_number' => 'nullable|string',
            'effective_start' => 'required|date',
            'effective_end' => 'required|date',
            'payment_allocation' => [
                'required',
                Rule::in(ClientPayer::$allocationTypes),
            ],
            'payment_allowance' => 'nullable|numeric|between:0,99999.99|required_if:payment_allocation,daily|required_if:payment_allocation,weekly|required_if:payment_allocation,monthly',
            'split_percentage' => 'nullable|numeric|between:0,1|required_if:payment_allocation,split',
        ];
    }

    /**
     * Filter the request data for processing.
     *
     * @return array
     */
    public function filtered() : array
    {
        $data = $this->validated();
        $data['effective_start'] = (new Carbon($data['effective_start']))->format('Y-m-d');
        $data['effective_end'] = (new Carbon($data['effective_end']))->format('Y-m-d');
        $data['split_percentage'] = empty($data['split_percentage']) ? null : $data['split_percentage'] / 100;

        // clear allocation fields depending on which allocation method is selected
        switch ($data['payment_allocation']) {
            case ClientPayer::ALLOCATION_BALANCE:
                $data['payment_allowance'] = null;
                $data['split_percentage'] = null;
                break;
            case ClientPayer::ALLOCATION_DAILY:
            case ClientPayer::ALLOCATION_WEEKLY:
            case ClientPayer::ALLOCATION_MONTHLY:
                $data['split_percentage'] = null;
                break;
            case ClientPayer::ALLOCATION_SPLIT:
                $data['payment_allowance'] = null;
                break;
        }

        return $data;
    }
}
