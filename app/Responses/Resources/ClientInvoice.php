<?php
namespace App\Responses\Resources;

use App\Billing\Exceptions\PaymentMethodError;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Collection;

class ClientInvoice extends Resource
{
    /**
     * The resource instance.
     *
     * @var \App\Billing\ClientInvoice
     */
    public $resource;

    /**
     * @param \Illuminate\Support\Collection $items
     * @return \Illuminate\Support\Collection
     */
    public function groupItems(Collection $items)
    {
        return $items->sortBy('date')->groupBy('group');
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->resource->attributesToArray() + [
            'client' => $this->whenLoaded('client'),
            'client_on_hold' => $this->whenLoaded('client', function() {
                return $this->resource->client->isOnHold();
            }),
            'payment_hold_notes' => $this->whenLoaded('client', function() {
                return optional( $this->resource->client->paymentHold )->notes;
            }),
            'payer' => $this->whenLoaded('clientPayer', function() {
                return $this->resource->getClientPayer()->getPayer();
            }),
            'payer_payment_type' => $this->whenLoaded('clientPayer', function() {
                try {
                    if ($method = $this->resource->getClientPayer()->getPaymentMethod()) {
                        return $method->getPaymentType();
                    }
                }
                catch (PaymentMethodError $e) {}
                return null;
            }),
            'items' => $this->whenLoaded('items', function() {
                return $this->groupItems($this->resource->items)->toArray();
            }),
            'payments' => $this->whenLoaded('payments'),
            'estimates' => $this->whenLoaded('clientPayer', function() {
                return [
                    'caregiver_total' => $this->resource->getEstimates()->getCaregiverTotal(),
                    'ally_total' => $this->resource->getEstimates()->getAllyTotal(),
                    'provider_total' => $this->resource->getEstimates()->getProviderTotal(),
                ];
            }),
            'was_split' => $this->whenLoaded('items', function() {
                return $this->resource->getWasSplit();
            }),
            'location' => $this->whenLoaded('client', function () {
                return $this->resource->client->business->name;
            }),
                'has_claim' => !empty($this->resource->claim)
        ];
    }
}
