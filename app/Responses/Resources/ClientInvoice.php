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

    private $estimates;

    /**
     * @param \Illuminate\Support\Collection $items
     * @return \Illuminate\Support\Collection
     */
    public function groupItems(Collection $items)
    {
        return $items->sort('date')->groupBy('group');
    }

    public function withEstimates($enable = true)
    {
        $this->estimates = $enable;
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
            'payer' => $this->whenLoaded('clientPayer', function() {
                return $this->resource->getClientPayer()->getPayer();
            }),
            'payer_payment_type' => $this->whenLoaded('clientPayer', function() {
                try {
                    if ($method = $this->resource->getClientPayer()->getPaymentMethod()) {
                        return $method->getPaymentStrategy()->getPaymentType();
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
        ];
    }
}
