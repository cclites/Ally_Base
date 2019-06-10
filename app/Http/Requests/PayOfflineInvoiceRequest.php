<?php

namespace App\Http\Requests;

use App\Billing\ClaimPayment;
use App\Billing\OfflineInvoicePayment;
use App\Billing\Payments\OfflinePayment;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class PayOfflineInvoiceRequest extends FormRequest
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
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|between:0,9999999.99',
            'type' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:4096',
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
        $data['payment_date'] = Carbon::parse($data['payment_date'])->format('Y-m-d');
        return $data;
    }

    public function toOfflineInvoicePayment() : OfflineInvoicePayment
    {
        return new OfflineInvoicePayment([
            'payment_date' => $this->filtered()['payment_date'],
            'amount' => $this->filtered()['amount'],
            'type' => $this->filtered()['type'] ?? null,
            'reference' => $this->filtered()['reference'] ?? null,
            'notes' => $this->filtered()['notes'] ?? null,
        ]);
    }

    public function getAmount(): float
    {
        return (float) $this->input('amount');
    }
}