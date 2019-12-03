<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;
use App\Billing\ClientPayer;

class UpdateClientPayersRequest extends FormRequest
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
            'payers' => 'nullable|array',
            'payers.*.payer_id' => [
                'nullable',
                'numeric',
                Rule::exists('payers', 'id')->where(function ($query) {
                    $query->where('chain_id', $this->route('client')->business->chain_id)
                        ->orWhereNull('chain_id');
                })
            ],
            'payers.*.policy_number' => 'nullable|string',
            'payers.*.effective_start' => 'required|date',
            'payers.*.effective_end' => 'required|date',
            'payers.*.payment_allocation' => [
                'required',
                Rule::in(ClientPayer::$allocationTypes),
            ],
            'payers.*.payment_allowance' => 'nullable|numeric|between:0,99999.99|required_if:payment_allocation,daily|required_if:payment_allocation,weekly|required_if:payment_allocation,monthly',
            'payers.*.split_percentage' => 'nullable|numeric|between:0,100|required_if:payment_allocation,split',
            'payers.*.notes' => 'nullable|string',
            'payers.*.cirts_number' => 'nullable|string|max:32',
            'payers.*.program_number' => 'nullable|string|max:32',
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
        if (isset($data['payers'])) {
            $data['payers'] = collect($data['payers'])->map(function ($payer) {
                unset($payer['payer_name']);
                unset($payer['payer']);
                $data = array_merge($payer, [
                    'effective_start' => (new Carbon($payer['effective_start']))->format('Y-m-d'),
                    'effective_end' => (new Carbon($payer['effective_end']))->format('Y-m-d'),
                    'split_percentage' => empty($payer['split_percentage']) ? 0 : $payer['split_percentage'] / 100,
                ]);

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
            })->toArray();
        } else {
            $data['payers'] = [];
        }

        // automatically fill the priority values in order of shown
        for ($i = 0; $i < count($data['payers']); $i++) {
            $data['payers'][$i]['priority'] = $i + 1;
        }

        return $data;
    }
}
