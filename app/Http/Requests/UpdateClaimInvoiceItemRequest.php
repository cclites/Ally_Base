<?php

namespace App\Http\Requests;

use App\ClaimableExpense;
use App\ClaimableService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class UpdateClaimInvoiceItemRequest extends FormRequest
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
            'name' => 'required_if:claimable_type,'.ClaimableExpense::class.'|string',
            'rate' => 'required_if:claimable_type,'.ClaimableExpense::class.'|numeric|min:0|max:999.99',
            'units' => 'required_if:claimable_type,'.ClaimableExpense::class.'|numeric|min:0|max:999.99',
            'notes' => 'required_if:claimable_type,'.ClaimableExpense::class.'|string',
            'date' => 'required_if:claimable_type,'.ClaimableExpense::class.'|date',
        ];
    }

    /**
     * Get the data to update the ClaimInvoiceItem's Claimable object.
     *
     * @param string $type
     * @return array
     */
    public function getClaimableData(string $type) : array
    {
        $data = collect($this->validated());

        switch ($type) {
            case ClaimableService::class:
                break;
            case ClaimableExpense::class:
                $data = $data->only(['name', 'notes', 'date'])
                    ->toArray();
                $data['date'] = filter_date($data['date']);
                break;
            default:
                return [];
        }

        return $data;
    }

    /**
     * Get the data to update the ClaimInvoiceItem.
     *
     * @param string $type
     * @return array
     */
    public function getClaimItemData(string $type) : array
    {
        switch ($type) {
            case ClaimableService::class:
                $data = collect($this->validated())
                    ->only(['rate', 'units'])
                    ->toArray();

                break;
            case ClaimableExpense::class:
                $data = collect($this->validated())
                    ->only(['rate', 'units', 'date'])
                    ->toArray();

                $data['date'] = Carbon::parse($data['date'], auth()->user()->officeUser->getTimezone())->setTimezone('UTC');
                break;
            default:
                return [];
        }

        $data['amount'] = multiply(floatval($data['rate']), floatval($data['units']));

        // TODO: Validate amount against the total amount of payments applied towards this item, this value cannot be be less.

        return $data;
    }
}
