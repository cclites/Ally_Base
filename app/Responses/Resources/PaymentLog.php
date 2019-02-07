<?php
namespace App\Responses\Resources;

use Illuminate\Http\Resources\Json\Resource;

class PaymentLog extends Resource
{
    /**
     * The resource instance.
     *
     * @var \App\Billing\PaymentLog
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'log_id' => $this->resource->id,
            'batch_id' => $this->resource->batch_id,
            'payment_id' => $this->resource->payment_id,
            'payment_method' => optional($this->resource->method)->getDisplayValue(),
            'amount' => $this->resource->payment->amount ?? null,
            'success' => $this->resource->payment->success ?? false,
            'exception' => $this->resource->exception,
            'error_message' => $this->resource->error_message,
            'invoices' => $this->resource->payment->invoices ?? [],
        ];
    }
}